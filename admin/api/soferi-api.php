<?php
header("Content-Type: application/json; charset=UTF-8");

require "../../database/db_config.php";

mysqli_report(MYSQLI_REPORT_OFF);

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
    $obligati_lauki = ['vards', 'uzvards', 'talrunis', 'licences_nr'];
    foreach ($obligati_lauki as $lauks) {
        if (!isset($pieteikums[$lauks]) || trim((string)$pieteikums[$lauks]) === '') {
            kluda(400, "Lauks '$lauks' ir obligāts un nevar būt tukšs");
        }
    }

    $limiti = ['vards' => 50, 'uzvards' => 50, 'talrunis' => 20, 'licences_nr' => 20];
    foreach ($limiti as $lauks => $max) {
        if (mb_strlen(trim($pieteikums[$lauks])) > $max) {
            kluda(400, "Lauks '$lauks' nedrīkst būt garāks par $max simboliem");
        }
    }

    if (!preg_match('/^\+?[0-9 ]{8,20}$/', trim($pieteikums['talrunis']))) {
        kluda(400, "Nederīgs tālruņa numura formāts");
    }
}

// Atgriež auto_id vai null, ja automašīna nav izvēlēta
function auto_id_vertiba($pieteikums) {
    global $savienojums;

    if (!isset($pieteikums['auto_id']) || $pieteikums['auto_id'] === '') return null;

    $auto_id = (int) $pieteikums['auto_id'];
    $sql = $savienojums->prepare("SELECT auto_id FROM autoparks WHERE auto_id = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $auto_id);
    $sql->execute();
    $eksiste = $sql->get_result()->num_rows > 0;
    $sql->close();

    if (!$eksiste) kluda(400, "Izvēlētā automašīna nav atrasta");
    return $auto_id;
}

$metode = $_SERVER['REQUEST_METHOD'];

$izvele = "SELECT s.sofers_id, s.vards, s.uzvards, s.talrunis, s.licences_nr, s.auto_id, s.statuss,
                  a.numurs AS auto_numurs, a.marka, a.modelis
           FROM soferi s
           LEFT JOIN autoparks a ON a.auto_id = s.auto_id";

if ($metode === 'GET') {
    if (isset($_GET['auto_saraksts'])) {
        // Automašīnu saraksts izvēlnei
        $rezultats = $savienojums->query("SELECT auto_id, numurs, marka, modelis FROM autoparks WHERE statuss = 'aktīvs' ORDER BY numurs");
        $autos = [];
        while ($auto = $rezultats->fetch_assoc()) {
            $autos[] = $auto;
        }
        echo json_encode($autos);
    }
    elseif (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("$izvele WHERE s.sofers_id = ? AND s.statuss = 'aktīvs'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            kluda(404, "Šoferis nav atrasts");
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("$izvele WHERE s.statuss = 'aktīvs' ORDER BY s.sofers_id DESC");
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

    $vards = trim($pieteikums['vards']);
    $uzvards = trim($pieteikums['uzvards']);
    $talrunis = trim($pieteikums['talrunis']);
    $licences_nr = strtoupper(trim($pieteikums['licences_nr']));
    $auto_id = auto_id_vertiba($pieteikums);

    $sql = $savienojums->prepare("INSERT INTO soferi (vards, uzvards, talrunis, licences_nr, auto_id) VALUES (?, ?, ?, ?, ?)");
    $sql->bind_param("ssssi", $vards, $uzvards, $talrunis, $licences_nr, $auto_id);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Šoferis pievienots", "sofers_id" => $sql->insert_id]);
    } else {
        if ($sql->errno === 1062) {
            kluda(409, "Šoferis ar šādu apliecības numuru jau eksistē");
        } else {
            kluda(500, "Neizdevās pievienot šoferi datubāzē");
        }
    }
    $sql->close();
}
elseif ($metode === 'PUT') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT sofers_id FROM soferi WHERE sofers_id = ? AND statuss = 'aktīvs'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        kluda(404, "Šoferis ar norādīto ID nav atrasts");
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);
    if (!$pieteikums) kluda(400, "Nederīgs JSON");

    validet_laukus($pieteikums);

    $vards = trim($pieteikums['vards']);
    $uzvards = trim($pieteikums['uzvards']);
    $talrunis = trim($pieteikums['talrunis']);
    $licences_nr = strtoupper(trim($pieteikums['licences_nr']));
    $auto_id = auto_id_vertiba($pieteikums);

    $sql = $savienojums->prepare("UPDATE soferi SET vards=?, uzvards=?, talrunis=?, licences_nr=?, auto_id=? WHERE sofers_id = ?");
    $sql->bind_param("ssssii", $vards, $uzvards, $talrunis, $licences_nr, $auto_id, $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Šoferis atjaunots"]);
    } else {
        if ($sql->errno === 1062) {
            kluda(409, "Šoferis ar šādu apliecības numuru jau eksistē");
        } else {
            kluda(500, "Neizdevās atjaunot šoferi");
        }
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $sql = $savienojums->prepare("UPDATE soferi SET statuss = 'dzēsts' WHERE sofers_id = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) kluda(500, "Neizdevās dzēst šoferi");
    if ($sql->affected_rows === 0) kluda(404, "Šoferis nav atrasts");

    echo json_encode(["message" => "Šoferis veiksmīgi dzēsts"]);
    $sql->close();
}
else {
    kluda(405, "Metode nav atbalstīta");
}

$savienojums->close();