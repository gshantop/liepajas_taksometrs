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

function kluda($kods, $teksts) {
    http_response_code($kods);
    echo json_encode(["error" => $teksts]);
    exit;
}

// Palīgfunkcija datu validācijai:
function validet_laukus($pieteikums) {
    foreach (['vards', 'talrunis'] as $lauks) {
        if (!isset($pieteikums[$lauks]) || trim((string)$pieteikums[$lauks]) === '') {
            kluda(400, "Lauks '$lauks' ir obligāts un nevar būt tukšs");
        }
    }

    $limiti = ['vards' => 100, 'talrunis' => 20, 'epasts' => 100];
    foreach ($limiti as $lauks => $max) {
        if (isset($pieteikums[$lauks]) && mb_strlen(trim($pieteikums[$lauks])) > $max) {
            kluda(400, "Lauks '$lauks' nedrīkst būt garāks par $max simboliem");
        }
    }

    if (!preg_match('/^\+?[0-9 ]{8,20}$/', trim($pieteikums['talrunis']))) {
        kluda(400, "Nederīgs tālruņa numura formāts");
    }

    $epasts = trim($pieteikums['epasts'] ?? '');
    if ($epasts !== '' && !filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
        kluda(400, "Nederīgs e-pasta formāts");
    }
}

$metode = $_SERVER['REQUEST_METHOD'];

// Klientus pievieno klienta puse pēc pasūtījuma, tāpēc POST šeit nav
if ($metode === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("SELECT * FROM klienti WHERE klients_id = ? AND statuss = 'aktīvs'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            kluda(404, "Klients nav atrasts");
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("SELECT * FROM klienti WHERE statuss = 'aktīvs' ORDER BY klients_id DESC");
        $pieteikumi = [];
        while ($pieteikums = $rezultats->fetch_assoc()) {
            $pieteikumi[] = $pieteikums;
        }
        echo json_encode($pieteikumi);
    }
}
elseif ($metode === 'PUT') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT klients_id FROM klienti WHERE klients_id = ? AND statuss = 'aktīvs'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        kluda(404, "Klients ar norādīto ID nav atrasts");
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);
    if (!$pieteikums) kluda(400, "Nederīgs JSON");

    validet_laukus($pieteikums);

    $vards = trim($pieteikums['vards']);
    $talrunis = trim($pieteikums['talrunis']);
    $epasts = trim($pieteikums['epasts'] ?? '');
    $epasts = $epasts === '' ? null : $epasts;

    $sql = $savienojums->prepare("UPDATE klienti SET vards=?, talrunis=?, epasts=? WHERE klients_id = ?");
    $sql->bind_param("sssi", $vards, $talrunis, $epasts, $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Klients atjaunots"]);
    } else {
        if ($sql->errno === 1062) {
            kluda(409, "Klients ar šādu tālruņa numuru jau eksistē");
        } else {
            kluda(500, "Neizdevās atjaunot klientu");
        }
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $sql = $savienojums->prepare("UPDATE klienti SET statuss = 'dzēsts' WHERE klients_id = ? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) kluda(500, "Neizdevās dzēst klientu");
    if ($sql->affected_rows === 0) kluda(404, "Klients nav atrasts");

    echo json_encode(["message" => "Klients veiksmīgi dzēsts"]);
    $sql->close();
}
else {
    kluda(405, "Metode nav atbalstīta");
}

$savienojums->close();