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

// Tikai Admin loma drīkst izmantot šo API
$loma_sql = $savienojums->prepare("SELECT loma FROM darbinieki WHERE lietotajvards = ? AND statuss = 'aktīvs'");
$loma_sql->bind_param("s", $_SESSION['lietotajvards_GSHT']);
$loma_sql->execute();
$esosa_loma = $loma_sql->get_result()->fetch_assoc();
$loma_sql->close();

if (!$esosa_loma || $esosa_loma['loma'] !== 'Admin') {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Piekļuve atļauta tikai administratoriem"]);
    exit;
}

if (!isset($_SESSION['lietotajvards_GSHT'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Nepieciešama autorizācija"]);
    exit;
}

// Palīgfunkcija datu validācijai:
// $parole_obligata = true (POST), false (PUT — parole nav obligāta)
function validet_laukus($pieteikums, $parole_obligata = true) {
    // Obligātie ievadlauki:
    $obligati_lauki = ['lietotajvards', 'vards', 'uzvards', 'epasts', 'loma'];
    if ($parole_obligata) $obligati_lauki[] = 'parole';

    foreach ($obligati_lauki as $lauks) {
        if (!isset($pieteikums[$lauks]) || trim($pieteikums[$lauks]) === '') {
            http_response_code(400); // Bad Request
            echo json_encode(["error" => "Lauks '$lauks' ir obligāts un nevar būt tukšs"]);
            exit;
        }
    }

    // E-pasta validācija:
    if (!filter_var(trim($pieteikums['epasts']), FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nederīgs e-pasta formāts"]);
        exit;
    }

    // Lomas validācija (enum 'Admin', 'Moder'):
    if (!in_array($pieteikums['loma'], ['Admin', 'Moder'], true)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Loma var būt tikai Admin vai Moder"]);
        exit;
    }
}

$metode = $_SERVER['REQUEST_METHOD'];

if ($metode === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("SELECT lietotajs_id, lietotajvards, loma, vards, uzvards, epasts, statuss FROM darbinieki WHERE lietotajs_id =?");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["error" => "Lietotajs nav atrasts"]);
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("SELECT lietotajs_id, lietotajvards, loma, vards, uzvards, epasts, statuss FROM darbinieki ORDER BY lietotajs_id DESC");
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

    validet_laukus($pieteikums, true);

    $hash = password_hash($pieteikums['parole'], PASSWORD_BCRYPT); // 60 simboli = varchar(60)

    $sql = $savienojums->prepare("INSERT INTO darbinieki (lietotajvards, parole, loma, vards, uzvards, epasts) VALUES (?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssssss", $pieteikums['lietotajvards'], $hash, $pieteikums['loma'], $pieteikums['vards'], $pieteikums['uzvards'], $pieteikums['epasts']);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Lietotajs pievienots", "lietotajs_id" => $sql->insert_id]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Neizdevās pievienot lietotaju datubāzē"]);
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
    
    $parbaude = $savienojums->prepare("SELECT lietotajs_id FROM darbinieki WHERE lietotajs_id =?");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    if ($parbaude->get_result()->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "Lietotajs ar norādīto ID nav atrasts"]);
        exit;
    }
    $parbaude->close();

    $pieteikums = json_decode(file_get_contents("php://input"), true);

    if (!$pieteikums) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Nederīgs JSON"]);
        exit;
    }

    validet_laukus($pieteikums, false);

    // Statuss enum('aktīvs', 'dzēsts'), pēc noklusējuma 'aktīvs'
    $statuss = $pieteikums['statuss'] ?? 'aktīvs';
    if (!in_array($statuss, ['aktīvs', 'dzēsts'], true)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Statuss var būt tikai aktīvs vai dzēsts"]);
        exit;
    }

    if (trim($pieteikums['parole'] ?? '') !== '') {
        // Parole nosūtīta — atjauno arī paroli
        $hash = password_hash($pieteikums['parole'], PASSWORD_BCRYPT);
        $sql = $savienojums->prepare("UPDATE darbinieki SET lietotajvards=?, parole=?, loma=?, vards=?, uzvards=?, epasts=?, statuss=? WHERE lietotajs_id =?");
        $sql->bind_param("sssssssi", $pieteikums['lietotajvards'], $hash, $pieteikums['loma'], $pieteikums['vards'], $pieteikums['uzvards'], $pieteikums['epasts'], $statuss, $id);
    } else {
        // Parole nav nosūtīta — vecā paliek nemainīta
        $sql = $savienojums->prepare("UPDATE darbinieki SET lietotajvards=?, loma=?, vards=?, uzvards=?, epasts=?, statuss=? WHERE lietotajs_id =?");
        $sql->bind_param("ssssssi", $pieteikums['lietotajvards'], $pieteikums['loma'], $pieteikums['vards'], $pieteikums['uzvards'], $pieteikums['epasts'], $statuss, $id);
    }

    if ($sql->execute()) {
        echo json_encode(["message" => "Lietotajs atjaunots"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Neizdevās atjaunot lietotaju"]);
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
    $sql = $savienojums->prepare("UPDATE darbinieki SET statuss = 'dzēsts' WHERE lietotajs_id =?");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Neizdevās dzēst lietotaju"]);
        exit;
    }

    if ($sql->affected_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "Lietotajs nav atrasts"]);
        exit;
    }

    echo json_encode(["message" => "Lietotajs veiksmīgi dzēsts"]);
    $sql->close();
} 
else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Metode nav atbalstīta"]);
}

$savienojums->close();