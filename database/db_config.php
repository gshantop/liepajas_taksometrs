<?php
    $serveris = "localhost";
    $lietotajs = "grobina1_cecotkins";
    $parole = "Kgzc2Z9468@";
    $datubaze = "grobina1_cecotkins";

    $savienojums = mysqli_connect($serveris, $lietotajs, $parole, $datubaze);

    if(!$savienojums){
        die("Nav izveidots savienojums ar datubāzi: " . mysqli_connect_error());
    }
?>