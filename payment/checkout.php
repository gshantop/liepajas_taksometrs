<?php
session_start();
require __DIR__ . "/../database/db_config.php";
require_once __DIR__ . "/config.php";

header('Content-Type: application/json; charset=utf-8');

function fail($msg) {
    echo json_encode(['ok' => false, 'kluda' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Nepareiza pieprasījuma metode');

$vards  = trim($_POST['vards'] ?? '');
$epasts = trim($_POST['epasts'] ?? '');
$no     = trim($_POST['no'] ?? '');
$uz     = trim($_POST['uz'] ?? '');
$tarifs = (int) ($_POST['tarifs'] ?? 0);
$km     = (float) ($_POST['km'] ?? 0);
$maksa  = ($_POST['maksa'] ?? '') === 'karte' ? 'karte' : 'nauda';

// Tālrunis: tikai cipari, bez 371
$talr = preg_replace('/\D+/', '', $_POST['talrunis'] ?? '');
if (strlen($talr) === 11 && substr($talr, 0, 3) === '371') {
    $talr = substr($talr, 3);
}

if ($vards === '' || $no === '' || $uz === '') fail('Aizpildi visus laukus');
if (!preg_match('/^\d{8}$/', $talr)) fail('Nepareizs tālruņa numurs (8 cipari)');
if ($epasts === '' || strlen($epasts) > 100 || !filter_var($epasts, FILTER_VALIDATE_EMAIL)) {
    fail('Nepareizs e-pasta formāts');
}
if ($km < 0.5 || $km > 500) fail('Nepareizs attālums');

// Tarifs no DB (cenu rēķinām serverī, nevis ņemam no formas)
$st = $savienojums->prepare(
    "SELECT iekrausanas_maksa, cena_km FROM tarifi WHERE tarifs_id = ? AND statuss = 'aktīvs'"
);
$st->bind_param("i", $tarifs);
$st->execute();
$t = $st->get_result()->fetch_assoc();
if (!$t) fail('Tarifs nav atrasts');

$summa = round($t['iekrausanas_maksa'] + $t['cena_km'] * $km, 2);

// Klients: atrodam pēc tālruņa vai izveidojam
$st = $savienojums->prepare("SELECT klients_id FROM klienti WHERE talrunis = ? LIMIT 1");
$st->bind_param("s", $talr);
$st->execute();
$k = $st->get_result()->fetch_assoc();

if ($k) {
    // Esošam klientam atjaunojam vārdu, e-pastu un statusu (arī dzēstam)
    $klients = (int) $k['klients_id'];
    $st = $savienojums->prepare(
        "UPDATE klienti SET statuss = 'aktīvs', vards = ?, epasts = ? WHERE klients_id = ?"
    );
    $st->bind_param("ssi", $vards, $epasts, $klients);
    if (!$st->execute()) fail('Neizdevās atjaunot klientu');
} else {
    $st = $savienojums->prepare("INSERT INTO klienti (vards, talrunis, epasts) VALUES (?, ?, ?)");
    $st->bind_param("sss", $vards, $talr, $epasts);
    if (!$st->execute()) fail('Neizdevās saglabāt klientu');
    $klients = $savienojums->insert_id;
}

// Brauciens
$apmaksa = $maksa === 'karte' ? 'gaida' : 'nav';
$st = $savienojums->prepare(
    "INSERT INTO braucieni (klients_id, tarifs_id, no_adrese, uz_adrese, km, maksas_veids, apmaksa, aptuvena_cena)
     VALUES (?,?,?,?,?,?,?,?)"
);
$st->bind_param("iissdssd", $klients, $tarifs, $no, $uz, $km, $maksa, $apmaksa, $summa);
if (!$st->execute()) fail('Neizdevās saglabāt pasūtījumu');
$id = $savienojums->insert_id;

// Skaidra nauda: uzreiz atpakaļ uz sākumlapu
if ($maksa === 'nauda') {
    $_SESSION['pazinojums'] = "order_success";
    echo json_encode(['ok' => true, 'url' => BASE_URL . '/']);
    exit;
}

// Karte: Stripe Checkout
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "locale" => "lv",
        "customer_email" => $epasts,
        "client_reference_id" => (string) $id,
        "success_url" => BASE_URL . "/payment/success.php?session_id={CHECKOUT_SESSION_ID}",
        "cancel_url"  => BASE_URL . "/",
        "line_items" => [[
            "quantity" => 1,
            "price_data" => [
                "currency" => "eur",
                "unit_amount" => (int) round($summa * 100),
                "product_data" => [
                    "name" => "Taksometrs: $no → $uz"
                ]
            ]
        ]]
    ]);

    $st = $savienojums->prepare("UPDATE braucieni SET stripe_session_id = ? WHERE brauciens_id = ?");
    $sid = $checkout_session->id;
    $st->bind_param("si", $sid, $id);
    $st->execute();

    echo json_encode(['ok' => true, 'url' => $checkout_session->url]);
} catch (Exception $e) {
    error_log($e->getMessage());
    fail('Maksājuma kļūda, mēģini vēlreiz');
}