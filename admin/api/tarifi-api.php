<?php
header("Content-Type: application/json; charset=UTF-8");

require "../../database/db_config.php";

mysqli_report(MYSQLI_REPORT_OFF); // mysqli neizmet izņēmumus — kļūdas apstrādā kods (piem., dublikāts 1062)

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

function kluda($kods, $teksts) {
    http_response_code($kods);
    echo json_encode(["error" => $teksts]);
    exit;
}

// Palīgfunkcija datu validācijai:
function validet_laukus($pieteikums) {

    $kods = strtolower(trim((string) ($pieteikums['kods'] ?? '')));
    if ($kods === '') kluda(400, "Lauks 'kods' ir obligāts un nevar būt tukšs");
    if (!preg_match('/^[a-z0-9]{2,10}$/', $kods)) {
        kluda(400, "Kodam jāsastāv no 2–10 latīņu burtiem un cipariem");
    }

    $nosaukums = preg_replace('/\s+/u', ' ', trim((string) ($pieteikums['nosaukums'] ?? '')));
    if ($nosaukums === '') kluda(400, "Lauks 'nosaukums' ir obligāts un nevar būt tukšs");
    if (mb_strlen($nosaukums) < 2 || mb_strlen($nosaukums) > 50 || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} .,()\/\-–]*$/u', $nosaukums)) {
        kluda(400, "Nosaukums: tikai burti, cipari, atstarpes un simboli . , ( ) / - (2–50 simboli)");
    }

    if (filter_var($pieteikums['auto_id'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false) {
        kluda(400, "Jāizvēlas automašīna no autoparka");
    }

    foreach ([['iekrausanas_maksa', 0, 100], ['cena_km', 0.01, 50], ['cena_min', 0, 10]] as [$lauks, $min, $max]) {
        $v = trim((string) ($pieteikums[$lauks] ?? ''));
        if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $v) || $v < $min || $v > $max) {
            kluda(400, "Lauks '$lauks' jābūt skaitlim no $min līdz $max (līdz 2 cipariem aiz komata)");
        }
    }

    $birka = preg_replace('/\s+/u', ' ', trim((string) ($pieteikums['birka'] ?? '')));
    if ($birka !== '' && (mb_strlen($birka) > 30 || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} .,\-]*$/u', $birka))) {
        kluda(400, "Birka: tikai burti, cipari, atstarpes un simboli . , - (līdz 30 simboliem)");
    }
}

// Atrod izvēlēto automašīnu autoparkā un atgriež tās datus (automasina teksts un vietu skaits)
function atrast_auto($auto_id) {
    global $savienojums;

    $sql = $savienojums->prepare("SELECT marka, modelis, vietu_skaits FROM autoparks WHERE auto_id = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $auto_id);
    $sql->execute();
    $auto = $sql->get_result()->fetch_assoc();
    $sql->close();

    if (!$auto) kluda(400, "Izvēlētā automašīna nav atrasta autoparkā");

    return [
        'automasina'   => mb_substr($auto['marka'] . ' ' . $auto['modelis'], 0, 100),
        'vietu_skaits' => (int) $auto['vietu_skaits']
    ];
}

$metode = $_SERVER['REQUEST_METHOD'];

$izvele = "SELECT t.tarifs_id, t.kods, t.nosaukums, t.auto_id, t.automasina,
                  COALESCE(a.vietu_skaits, t.vietu_skaits) AS vietu_skaits,
                  t.iekrausanas_maksa, t.cena_km, t.cena_min, t.birka, t.statuss,
                  a.numurs AS auto_numurs, a.marka, a.modelis
           FROM tarifi t
           LEFT JOIN autoparks a ON a.auto_id = t.auto_id";

if ($metode === 'GET') {
    if (isset($_GET['auto_saraksts'])) {
        // Automašīnu saraksts izvēlnei
        $rezultats = $savienojums->query("SELECT auto_id, numurs, marka, modelis, vietu_skaits FROM autoparks WHERE statuss = 'aktīvs' ORDER BY marka, modelis, numurs");
        $autos = [];
        while ($auto = $rezultats->fetch_assoc()) {
            $autos[] = $auto;
        }
        echo json_encode($autos);
    }
    elseif (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("$izvele WHERE t.tarifs_id = ? AND t.statuss = 'aktīvs'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            kluda(404, "Tarifs nav atrasts");
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("$izvele WHERE t.statuss = 'aktīvs' ORDER BY t.tarifs_id DESC");
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

    validet_laukus($pieteikums);

    $kods = strtolower(trim($pieteikums['kods']));
    $nosaukums = trim($pieteikums['nosaukums']);
    $auto_id = (int) $pieteikums['auto_id'];
    $auto = atrast_auto($auto_id);
    $automasina = $auto['automasina'];
    $vietu_skaits = $auto['vietu_skaits'];
    $iekrausanas_maksa = (float) $pieteikums['iekrausanas_maksa'];
    $cena_km = (float) $pieteikums['cena_km'];
    $cena_min = (float) $pieteikums['cena_min'];
    $birka = trim($pieteikums['birka'] ?? '');

    $sql = $savienojums->prepare("INSERT INTO tarifi (kods, nosaukums, auto_id, automasina, vietu_skaits, iekrausanas_maksa, cena_km, cena_min, birka) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssisiddds", $kods, $nosaukums, $auto_id, $automasina, $vietu_skaits, $iekrausanas_maksa, $cena_km, $cena_min, $birka);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Tarifs pievienots", "tarifs_id" => $sql->insert_id]);
    } else {
        if ($sql->errno === 1062) {
            kluda(409, "Tarifs ar šādu kodu jau eksistē");
        } else {
            kluda(500, "Neizdevās pievienot tarifu datubāzē");
        }
    }
    $sql->close();
}
elseif ($metode === 'PUT') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT tarifs_id FROM tarifi WHERE tarifs_id = ? AND statuss = 'aktīvs'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        kluda(404, "Tarifs ar norādīto ID nav atrasts");
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);
    if (!$pieteikums) kluda(400, "Nederīgs JSON");

    validet_laukus($pieteikums);

    $kods = strtolower(trim($pieteikums['kods']));
    $nosaukums = trim($pieteikums['nosaukums']);
    $auto_id = (int) $pieteikums['auto_id'];
    $auto = atrast_auto($auto_id);
    $automasina = $auto['automasina'];
    $vietu_skaits = $auto['vietu_skaits'];
    $iekrausanas_maksa = (float) $pieteikums['iekrausanas_maksa'];
    $cena_km = (float) $pieteikums['cena_km'];
    $cena_min = (float) $pieteikums['cena_min'];
    $birka = trim($pieteikums['birka'] ?? '');

    $sql = $savienojums->prepare("UPDATE tarifi SET kods=?, nosaukums=?, auto_id=?, automasina=?, vietu_skaits=?, iekrausanas_maksa=?, cena_km=?, cena_min=?, birka=? WHERE tarifs_id = ?");
    $sql->bind_param("ssisidddsi", $kods, $nosaukums, $auto_id, $automasina, $vietu_skaits, $iekrausanas_maksa, $cena_km, $cena_min, $birka, $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Tarifs atjaunots"]);
    } else {
        if ($sql->errno === 1062) {
            kluda(409, "Tarifs ar šādu kodu jau eksistē");
        } else {
            kluda(500, "Neizdevās atjaunot tarifu");
        }
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $sql = $savienojums->prepare("UPDATE tarifi SET statuss = 'dzēsts' WHERE tarifs_id = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) kluda(500, "Neizdevās dzēst tarifu");
    if ($sql->affected_rows === 0) kluda(404, "Tarifs nav atrasts");

    echo json_encode(["message" => "Tarifs veiksmīgi dzēsts"]);
    $sql->close();
}
else {
    kluda(405, "Metode nav atbalstīta");
}

$savienojums->close();