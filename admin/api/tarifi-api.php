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

// Palīgfunkcija datu validācijai:
function validet_laukus($pieteikums) {
    // Obligātie ievadlauki:
    $obligati_lauki = ['kods', 'nosaukums', 'automasina', 'vietu_skaits', 'iekrausanas_maksa', 'cena_km', 'cena_min'];
    foreach ($obligati_lauki as $lauks) {
        if (!isset($pieteikums[$lauks]) || trim((string)$pieteikums[$lauks]) === '') {
            http_response_code(400); // Bad Request
            echo json_encode(["error" => "Lauks '$lauks' ir obligāts un nevar būt tukšs"]);
            exit;
        }
    }

    // Teksta garuma ierobežojumi pēc tabulas struktūras:
    $limiti = ['kods' => 10, 'nosaukums' => 50, 'automasina' => 100, 'birka' => 30];
    foreach ($limiti as $lauks => $max) {
        if (isset($pieteikums[$lauks]) && mb_strlen(trim($pieteikums[$lauks])) > $max) {
            http_response_code(400); // Bad Request
            echo json_encode(["error" => "Lauks '$lauks' nedrīkst būt garāks par $max simboliem"]);
            exit;
        }
    }

    // Vietu skaits (tinyint):
    if (filter_var($pieteikums['vietu_skaits'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 99]]) === false) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Vietu skaitam jābūt veselam skaitlim no 1 līdz 99"]);
        exit;
    }

    // Cenas (decimal(6,2) — līdz 9999.99):
    foreach (['iekrausanas_maksa', 'cena_km', 'cena_min'] as $lauks) {
        if (!is_numeric($pieteikums[$lauks]) || $pieteikums[$lauks] < 0 || $pieteikums[$lauks] > 9999.99) {
            http_response_code(400); // Bad Request
            echo json_encode(["error" => "Lauks '$lauks' jābūt skaitlim no 0 līdz 9999.99"]);
            exit;
        }
    }
}

$metode = $_SERVER['REQUEST_METHOD'];

if ($metode === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("SELECT * FROM tarifi WHERE tarifs_id =? AND statuss = 'aktīvs'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["error" => "Tarifs nav atrasts"]);
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("SELECT * FROM tarifi WHERE statuss = 'aktīvs' ORDER BY tarifs_id DESC");
        $pieteikumi = [];
        while ($pieteikums = $rezultats->fetch_assoc()) {
            $pieteikumi[] = $pieteikums;
        }
        echo json_encode($pieteikumi);
    }
}
elseif ($metode === 'POST') {
    $pieteikums = json_decode(file_get_contents("php://input"), true);

    if (!$pieteikums) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nederīgs JSON"]);
        exit;
    }

    validet_laukus($pieteikums);

    $kods = trim($pieteikums['kods']);
    $nosaukums = trim($pieteikums['nosaukums']);
    $automasina = trim($pieteikums['automasina']);
    $vietu_skaits = (int) $pieteikums['vietu_skaits'];
    $iekrausanas_maksa = (float) $pieteikums['iekrausanas_maksa'];
    $cena_km = (float) $pieteikums['cena_km'];
    $cena_min = (float) $pieteikums['cena_min'];
    $birka = trim($pieteikums['birka'] ?? '');

    $sql = $savienojums->prepare("INSERT INTO tarifi (kods, nosaukums, automasina, vietu_skaits, iekrausanas_maksa, cena_km, cena_min, birka) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("sssiddds", $kods, $nosaukums, $automasina, $vietu_skaits, $iekrausanas_maksa, $cena_km, $cena_min, $birka);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Tarifs pievienots", "tarifs_id" => $sql->insert_id]);
    } else {
        if ($sql->errno === 1062) {
            http_response_code(409); // Conflict
            echo json_encode(["error" => "Tarifs ar šādu kodu jau eksistē"]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["error" => "Neizdevās pievienot tarifu datubāzē"]);
        }
    }
    $sql->close();
}
elseif ($metode === 'PUT') {
    if (!isset($_GET['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT tarifs_id FROM tarifi WHERE tarifs_id =? AND statuss = 'aktīvs'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "Tarifs ar norādīto ID nav atrasts"]);
        exit;
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);

    if (!$pieteikums) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nederīgs JSON"]);
        exit;
    }

    validet_laukus($pieteikums);

    $kods = trim($pieteikums['kods']);
    $nosaukums = trim($pieteikums['nosaukums']);
    $automasina = trim($pieteikums['automasina']);
    $vietu_skaits = (int) $pieteikums['vietu_skaits'];
    $iekrausanas_maksa = (float) $pieteikums['iekrausanas_maksa'];
    $cena_km = (float) $pieteikums['cena_km'];
    $cena_min = (float) $pieteikums['cena_min'];
    $birka = trim($pieteikums['birka'] ?? '');

    $sql = $savienojums->prepare("UPDATE tarifi SET kods=?, nosaukums=?, automasina=?, vietu_skaits=?, iekrausanas_maksa=?, cena_km=?, cena_min=?, birka=? WHERE tarifs_id =?");
    $sql->bind_param("sssidddsi", $kods, $nosaukums, $automasina, $vietu_skaits, $iekrausanas_maksa, $cena_km, $cena_min, $birka, $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Tarifs atjaunots"]);
    } else {
        if ($sql->errno === 1062) {
            http_response_code(409); // Conflict
            echo json_encode(["error" => "Tarifs ar šādu kodu jau eksistē"]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["error" => "Neizdevās atjaunot tarifu"]);
        }
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }
    $id = (int) $_GET['id'];
    $sql = $savienojums->prepare("UPDATE tarifi SET statuss = 'dzēsts' WHERE tarifs_id =? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Neizdevās dzēst tarifu"]);
        exit;
    }

    if ($sql->affected_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "Tarifs nav atrasts"]);
        exit;
    }

    echo json_encode(["message" => "Tarifs veiksmīgi dzēsts"]);
    $sql->close();
}
else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Metode nav atbalstīta"]);
}

$savienojums->close();