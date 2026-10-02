<?php
header("Content-Type: application/json; charset=UTF-8");

require "../../database/db_config.php";

if ($savienojums->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Neizdevās pieslēgties datubāzei"]);
    exit;
}

session_start();
if (!isset($_SESSION['lietotajvards_GSHT'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Nepieciešama autorizācija"]);
    exit;
}

const STATUSI = ['jauns', 'procesā', 'pabeigts', 'atcelts'];

function kluda($kods, $teksts) {
    http_response_code($kods);
    echo json_encode(["error" => $teksts]);
    exit;
}

function neobligats($v) {
    return ($v === null || trim((string)$v) === '') ? null : $v;
}

// Pārbauda, vai aktīvs ieraksts eksistē (tabulu un kolonnu nosaukumi tiek padoti tikai no koda)
function parbaudit_id($tabula, $kolonna, $id, $kludas_teksts) {
    global $savienojums;

    $sql = $savienojums->prepare("SELECT $kolonna FROM $tabula WHERE $kolonna = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);
    $sql->execute();
    $eksiste = $sql->get_result()->num_rows > 0;
    $sql->close();

    if (!$eksiste) kluda(400, $kludas_teksts);
}

// Validē ievadi un atgriež sagatavotus datus (ar aprēķināto cenu)
function sagatavot_datus($d) {
    global $savienojums;

    foreach (['klients_id', 'tarifs_id', 'no_adrese', 'uz_adrese'] as $lauks) {
        if (!isset($d[$lauks]) || trim((string)$d[$lauks]) === '') {
            kluda(400, "Lauks '$lauks' ir obligāts un nevar būt tukšs");
        }
    }

    foreach (['no_adrese', 'uz_adrese'] as $lauks) {
        if (mb_strlen(trim($d[$lauks])) > 150) {
            kluda(400, "Lauks '$lauks' nedrīkst būt garāks par 150 simboliem");
        }
    }

    $klients_id = (int) $d['klients_id'];
    $tarifs_id = (int) $d['tarifs_id'];
    $sofers_id = neobligats($d['sofers_id'] ?? null);
    $sofers_id = $sofers_id === null ? null : (int) $sofers_id;

    parbaudit_id('klienti', 'klients_id', $klients_id, "Klients nav atrasts");
    parbaudit_id('tarifi', 'tarifs_id', $tarifs_id, "Tarifs nav atrasts");
    if ($sofers_id !== null) parbaudit_id('soferi', 'sofers_id', $sofers_id, "Šoferis nav atrasts");

    $statuss = $d['statuss'] ?? 'jauns';
    if (!in_array($statuss, STATUSI, true)) kluda(400, "Nederīgs statuss");

    $km = neobligats($d['km'] ?? null);
    $minutes = neobligats($d['minutes'] ?? null);

    if (($km === null) !== ($minutes === null)) {
        kluda(400, "Jānorāda gan kilometri, gan minūtes");
    }
    if ($statuss === 'pabeigts' && $km === null) {
        kluda(400, "Pabeigtam braucienam jānorāda kilometri un minūtes");
    }

    $cena = null;
    if ($km !== null) {
        if (!is_numeric($km) || $km < 0 || $km > 9999.99) {
            kluda(400, "Kilometriem jābūt skaitlim no 0 līdz 9999.99");
        }
        if (filter_var($minutes, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 9999]]) === false) {
            kluda(400, "Minūtēm jābūt veselam skaitlim no 0 līdz 9999");
        }
        $km = (float) $km;
        $minutes = (int) $minutes;

        // Cena = iekāpšanas maksa + km * cena_km + min * cena_min
        $sql = $savienojums->prepare("SELECT iekrausanas_maksa, cena_km, cena_min FROM tarifi WHERE tarifs_id = ?");
        $sql->bind_param("i", $tarifs_id);
        $sql->execute();
        $tarifs = $sql->get_result()->fetch_assoc();
        $sql->close();

        $cena = round($tarifs['iekrausanas_maksa'] + $km * $tarifs['cena_km'] + $minutes * $tarifs['cena_min'], 2);
        if ($cena > 999999.99) kluda(400, "Aprēķinātā cena ir pārāk liela");
    }

    return [
        'klients_id' => $klients_id,
        'sofers_id'  => $sofers_id,
        'tarifs_id'  => $tarifs_id,
        'no_adrese'  => trim($d['no_adrese']),
        'uz_adrese'  => trim($d['uz_adrese']),
        'km'         => $km,
        'minutes'    => $minutes,
        'cena'       => $cena,
        'statuss'    => $statuss
    ];
}

$metode = $_SERVER['REQUEST_METHOD'];

$izvele = "SELECT b.*, k.vards AS klients_vards, k.talrunis AS klients_talrunis,
                  t.nosaukums AS tarifs_nosaukums,
                  s.vards AS sofers_vards, s.uzvards AS sofers_uzvards,
                  a.numurs AS auto_numurs
           FROM braucieni b
           JOIN klienti k ON k.klients_id = b.klients_id
           JOIN tarifi t ON t.tarifs_id = b.tarifs_id
           LEFT JOIN soferi s ON s.sofers_id = b.sofers_id
           LEFT JOIN autoparks a ON a.auto_id = s.auto_id";

if ($metode === 'GET') {
    if (isset($_GET['saraksti'])) {
        // Izvēlņu saraksti formai
        $saraksti = ['klienti' => [], 'tarifi' => [], 'soferi' => []];

        $r = $savienojums->query("SELECT klients_id, vards, talrunis FROM klienti WHERE statuss = 'aktīvs' ORDER BY vards");
        while ($rinda = $r->fetch_assoc()) $saraksti['klienti'][] = $rinda;

        $r = $savienojums->query("SELECT tarifs_id, kods, nosaukums, iekrausanas_maksa, cena_km, cena_min FROM tarifi WHERE statuss = 'aktīvs' ORDER BY nosaukums");
        while ($rinda = $r->fetch_assoc()) $saraksti['tarifi'][] = $rinda;

        $r = $savienojums->query("SELECT sofers_id, vards, uzvards FROM soferi WHERE statuss = 'aktīvs' ORDER BY vards");
        while ($rinda = $r->fetch_assoc()) $saraksti['soferi'][] = $rinda;

        echo json_encode($saraksti);
    }
    elseif (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("$izvele WHERE b.brauciens_id = ? AND b.statuss <> 'dzēsts'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            kluda(404, "Brauciens nav atrasts");
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("$izvele WHERE b.statuss <> 'dzēsts' ORDER BY b.brauciens_id DESC");
        $pieteikumi = [];
        while ($pieteikums = $rezultats->fetch_assoc()) {
            $pieteikumi[] = $pieteikums;
        }
        echo json_encode($pieteikumi);
    }
}
elseif ($metode === 'POST') {
    $pieteikums = json_decode(file_get_contents("php://input"), true);
    if (!$pieteikums) kluda(400, "Nederīgs JSON");

    $a = sagatavot_datus($pieteikums);

    $sql = $savienojums->prepare("INSERT INTO braucieni (klients_id, sofers_id, tarifs_id, no_adrese, uz_adrese, km, minutes, cena, statuss) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("iiissdids", $a['klients_id'], $a['sofers_id'], $a['tarifs_id'], $a['no_adrese'], $a['uz_adrese'], $a['km'], $a['minutes'], $a['cena'], $a['statuss']);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Brauciens pievienots", "brauciens_id" => $sql->insert_id]);
    } else {
        kluda(500, "Neizdevās pievienot braucienu datubāzē");
    }
    $sql->close();
}
elseif ($metode === 'PUT') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT brauciens_id FROM braucieni WHERE brauciens_id = ? AND statuss <> 'dzēsts'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        kluda(404, "Brauciens ar norādīto ID nav atrasts");
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);
    if (!$pieteikums) kluda(400, "Nederīgs JSON");

    $a = sagatavot_datus($pieteikums);

    $sql = $savienojums->prepare("UPDATE braucieni SET klients_id=?, sofers_id=?, tarifs_id=?, no_adrese=?, uz_adrese=?, km=?, minutes=?, cena=?, statuss=? WHERE brauciens_id = ?");
    $sql->bind_param("iiissdidsi", $a['klients_id'], $a['sofers_id'], $a['tarifs_id'], $a['no_adrese'], $a['uz_adrese'], $a['km'], $a['minutes'], $a['cena'], $a['statuss'], $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Brauciens atjaunots"]);
    } else {
        kluda(500, "Neizdevās atjaunot braucienu");
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $sql = $savienojums->prepare("UPDATE braucieni SET statuss = 'dzēsts' WHERE brauciens_id = ? AND statuss <> 'dzēsts'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) kluda(500, "Neizdevās dzēst braucienu");
    if ($sql->affected_rows === 0) kluda(404, "Brauciens nav atrasts");

    echo json_encode(["message" => "Brauciens veiksmīgi dzēsts"]);
    $sql->close();
}
else {
    kluda(405, "Metode nav atbalstīta");
}

$savienojums->close();