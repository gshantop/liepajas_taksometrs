<?php
session_start();

if (!isset($_SESSION["lietotajvards_GSHT"])) {
    header("location: login.php");
    exit;
}

$page = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="lv" class="admin-dashboard">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taksometru pārvaldības sistēma</title>

    <!-- style.css atrodas saknes mapē, nevis admin mapē -->
    <link rel="stylesheet" href="../style.css?v=2">

    <!-- Fonti, kurus izmanto style.css -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;700;800&family=Archivo+Narrow:wght@700&display=swap">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <a href="main.php" class="logo">
            <i class="fa-solid fa-taxi"></i> Taksometru pārvaldība
        </a>

        <nav>
            <a href="main.php" class="btn <?= $page == 'main.php' ? 'active' : '' ?>">Kopsavilkums</a>
            <a href="autoparks.php" class="btn <?= $page == 'autoparks.php' ? 'active' : '' ?>">Auto parks</a>
            <a href="soferi.php" class="btn <?= $page == 'soferi.php' ? 'active' : '' ?>">Šoferi</a>
            <a href="klienti.php" class="btn <?= $page == 'klienti.php' ? 'active' : '' ?>">Klienti</a>
            <a href="braucieni.php" class="btn <?= $page == 'braucieni.php' ? 'active' : '' ?>">Braucieni</a>
            <a href="tarifi.php" class="btn <?= $page == 'tarifi.php' ? 'active' : '' ?>">Tarifi</a>
            <a href="lietotaji.php" class="btn <?= $page == 'lietotaji.php' ? 'active' : '' ?>">Lietotāji</a>
            <a href="#" class="btn"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION["lietotajvards_GSHT"]) ?></a>
            <a href="logout.php" class="btn" aria-label="Iziet"><i class="fas fa-power-off"></i></a>
        </nav>
    </header>
    <main>