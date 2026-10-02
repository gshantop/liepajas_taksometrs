<?php
session_start();
require __DIR__ . "/../database/db_config.php";
require_once __DIR__ . "/config.php";

if (!empty($_GET['session_id'])) {
    try {
        $checkout_session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);
        $id = (int) $checkout_session->client_reference_id;

        if ($checkout_session->payment_status === 'paid') {
            $sid = $checkout_session->id;
            $st = $savienojums->prepare(
                "UPDATE braucieni SET apmaksa = 'apmaksats'
                 WHERE brauciens_id = ? AND stripe_session_id = ?"
            );
            $st->bind_param("is", $id, $sid);
            $st->execute();
            $st->close();

            $_SESSION['pazinojums'] = "payment_success";
        } else {
            $_SESSION['pazinojums'] = "payment-error";
        }

        $savienojums->close();
    } catch (Exception $e) {
        error_log($e->getMessage());
        $_SESSION['pazinojums'] = "payment-empty";
    }
}

header("Location: ../");
exit;