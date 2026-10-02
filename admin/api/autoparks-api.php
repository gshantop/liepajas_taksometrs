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

const FOTO_DIR = __DIR__ . '/../uploads/autoparks/';
const FOTO_MAX = 3 * 1024 * 1024; // 3 MB

function kluda($kods, $teksts) {
    http_response_code($kods);
    echo json_encode(["error" => $teksts]);
    exit;
}

// Palīgfunkcija datu validācijai:
function validet_laukus($pieteikums) {
    // Valsts numurs: latīņu burti un cipari, līdz 8 simboliem, ne vairāk kā 4 cipari
    $numurs = strtoupper(trim((string) ($pieteikums['numurs'] ?? '')));
    if ($numurs === '') kluda(400, "Lauks 'numurs' ir obligāts un nevar būt tukšs");
    if (!preg_match('/^[A-Z0-9]{1,8}$/', $numurs)) {
        kluda(400, "Valsts numurs: tikai latīņu burti un cipari, līdz 8 simboliem");
    }
    if (preg_match_all('/[0-9]/', $numurs) > 4) {
        kluda(400, "Valsts numurā drīkst būt ne vairāk kā 4 cipari");
    }

    foreach (['marka' => 2, 'modelis' => 1] as $lauks => $min) {
        $v = preg_replace('/\s+/u', ' ', trim((string) ($pieteikums[$lauks] ?? '')));
        if ($v === '') kluda(400, "Lauks '$lauks' ir obligāts un nevar būt tukšs");
        if (mb_strlen($v) < $min || mb_strlen($v) > 50 || !preg_match('/^[\p{L}\p{N}][\p{L}\p{N} .\-]*$/u', $v)) {
            kluda(400, "Lauks '$lauks': tikai burti, cipari, atstarpes, punkts un defise ($min–50 simboli)");
        }
    }

    $krasa = preg_replace('/\s+/u', ' ', trim((string) ($pieteikums['krasa'] ?? '')));
    if ($krasa !== '' && (mb_strlen($krasa) < 2 || mb_strlen($krasa) > 30 || !preg_match('/^\p{L}[\p{L} \-]*$/u', $krasa))) {
        kluda(400, "Krāsa: tikai burti, atstarpes un defise (2–30 simboli)");
    }

    $max_gads = (int) date('Y') + 1;
    if (filter_var($pieteikums['gads'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1990, "max_range" => $max_gads]]) === false) {
        kluda(400, "Gadam jābūt no 1990 līdz $max_gads");
    }
    if (filter_var($pieteikums['vietu_skaits'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 2, "max_range" => 9]]) === false) {
        kluda(400, "Vietu skaitam jābūt veselam skaitlim no 2 līdz 9");
    }
}

// Saglabā augšupielādēto foto. Atgriež jauno faila nosaukumu vai null, ja fails nav nosūtīts
function saglabat_foto() {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $fails = $_FILES['foto'];

    if ($fails['error'] !== UPLOAD_ERR_OK) {
        kluda(400, "Neizdevās augšupielādēt failu");
    }
    if ($fails['size'] > FOTO_MAX) {
        kluda(400, "Foto nedrīkst būt lielāks par 3 MB");
    }

    // Tips pēc faila satura, nevis pēc nosaukuma
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $atlautie = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $finfo->file($fails['tmp_name']);

    if (!isset($atlautie[$mime])) {
        kluda(400, "Atļauti tikai JPG, PNG vai WEBP attēli");
    }

    if (!is_dir(FOTO_DIR)) mkdir(FOTO_DIR, 0755, true);

    $nosaukums = bin2hex(random_bytes(8)) . '.' . $atlautie[$mime];

    if (!move_uploaded_file($fails['tmp_name'], FOTO_DIR . $nosaukums)) {
        kluda(500, "Neizdevās saglabāt failu serverī");
    }

    return $nosaukums;
}

function dzest_foto($nosaukums) {
    if ($nosaukums && is_file(FOTO_DIR . $nosaukums)) {
        unlink(FOTO_DIR . $nosaukums);
    }
}

$metode = $_SERVER['REQUEST_METHOD'];

if ($metode === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $savienojums->prepare("SELECT * FROM autoparks WHERE auto_id =? AND statuss = 'aktīvs'");
        $sql->bind_param("i", $id);
        $sql->execute();
        $rezultats = $sql->get_result();

        if ($pieteikums = $rezultats->fetch_assoc()) {
            echo json_encode($pieteikums);
        } else {
            kluda(404, "Automašīna nav atrasta");
        }
        $sql->close();
    } else {
        $rezultats = $savienojums->query("SELECT * FROM autoparks WHERE statuss = 'aktīvs' ORDER BY auto_id DESC");
        $pieteikumi = [];
        while ($pieteikums = $rezultats->fetch_assoc()) {
            $pieteikumi[] = $pieteikums;
        }
        echo json_encode($pieteikumi);
    }
}
elseif ($metode === 'POST' && !isset($_GET['id'])) {
    // Jauna automašīna (multipart/form-data)
    $pieteikums = $_POST;

    validet_laukus($pieteikums);

    $numurs = strtoupper(trim($pieteikums['numurs']));
    $marka = trim($pieteikums['marka']);
    $modelis = trim($pieteikums['modelis']);
    $gads = (int) $pieteikums['gads'];
    $krasa = trim($pieteikums['krasa'] ?? '');
    $vietu_skaits = (int) $pieteikums['vietu_skaits'];
    $foto = saglabat_foto();

    $sql = $savienojums->prepare("INSERT INTO autoparks (numurs, marka, modelis, gads, krasa, vietu_skaits, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("sssisis", $numurs, $marka, $modelis, $gads, $krasa, $vietu_skaits, $foto);

    if ($sql->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Automašīna pievienota", "auto_id" => $sql->insert_id]);
    } else {
        dzest_foto($foto); // neatstāj nevajadzīgu failu
        if ($sql->errno === 1062) {
            kluda(409, "Automašīna ar šādu numuru jau eksistē");
        } else {
            kluda(500, "Neizdevās pievienot automašīnu datubāzē");
        }
    }
    $sql->close();
}
elseif ($metode === 'POST' && isset($_GET['id'])) {
    // Esošas automašīnas atjaunošana (POST ?id=..., jo PHP nelasa failus no PUT)
    $id = (int) $_GET['id'];

    $parbaude = $savienojums->prepare("SELECT foto FROM autoparks WHERE auto_id =? AND statuss = 'aktīvs'");
    $parbaude->bind_param("i", $id);
    $parbaude->execute();
    $esosa = $parbaude->get_result()->fetch_assoc();
    $parbaude->close();

    if (!$esosa) kluda(404, "Automašīna ar norādīto ID nav atrasta");

    $pieteikums = $_POST;

    validet_laukus($pieteikums);

    $numurs = strtoupper(trim($pieteikums['numurs']));
    $marka = trim($pieteikums['marka']);
    $modelis = trim($pieteikums['modelis']);
    $gads = (int) $pieteikums['gads'];
    $krasa = trim($pieteikums['krasa'] ?? '');
    $vietu_skaits = (int) $pieteikums['vietu_skaits'];

    $jauna_foto = saglabat_foto();
    $foto = $jauna_foto ?? $esosa['foto']; // ja jauna foto nav, paliek vecā

    $sql = $savienojums->prepare("UPDATE autoparks SET numurs=?, marka=?, modelis=?, gads=?, krasa=?, vietu_skaits=?, foto=? WHERE auto_id =?");
    $sql->bind_param("sssisisi", $numurs, $marka, $modelis, $gads, $krasa, $vietu_skaits, $foto, $id);

    if ($sql->execute()) {
        if ($jauna_foto) dzest_foto($esosa['foto']); // veco foto dzēš tikai pēc veiksmīgas saglabāšanas
        echo json_encode(["message" => "Automašīna atjaunota"]);
    } else {
        dzest_foto($jauna_foto);
        if ($sql->errno === 1062) {
            kluda(409, "Automašīna ar šādu numuru jau eksistē");
        } else {
            kluda(500, "Neizdevās atjaunot automašīnu");
        }
    }
    $sql->close();
}
elseif ($metode === 'DELETE') {
    if (!isset($_GET['id'])) kluda(400, "Nepieciešams ID");
    $id = (int) $_GET['id'];

    $sql = $savienojums->prepare("UPDATE autoparks SET statuss = 'dzēsts' WHERE auto_id =? AND statuss = 'aktīvs'");
    $sql->bind_param("i", $id);

    if (!$sql->execute()) kluda(500, "Neizdevās dzēst automašīnu");
    if ($sql->affected_rows === 0) kluda(404, "Automašīna nav atrasta");

    echo json_encode(["message" => "Automašīna veiksmīgi dzēsta"]);
    $sql->close();
}
else {
    kluda(405, "Metode nav atbalstīta");
}

$savienojums->close();