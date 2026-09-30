<?php
session_start();
if(!isset($_SESSION["lietotajvards_GSHT"])){
        header("location: login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="lv" class="admin-dashboard">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taksometru pārvaldības sistēma</title>
    <link rel="stylesheet" href="style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">
            <i class="fa-solid fa-taxi"></i> Taksometru pārvaldība
        </a>

        <nav>
            <a href="dashboard.php" class="btn <?= $page == 'dashboard.php' ? 'active' : '' ?>">Kopsavilkums</a>
            <a href="braucieni.php" class="btn <?= $page == 'braucieni.php' ? 'active' : '' ?>">Braucieni</a>
            <a href="klienti.php" class="btn <?= $page == 'klienti.php' ? 'active' : '' ?>">Klienti</a>
            <a href="noreikini.php" class="btn <?= $page == 'noreikini.php' ? 'active' : '' ?>">Norēķini</a>
            <a href="#" class="btn"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION["lietotajvards_GSHT"]) ?></a>
            <a href="logout.php" class="btn"><i class="fas fa-power-off"></i></a>
        </nav>
    </header>
    <main>