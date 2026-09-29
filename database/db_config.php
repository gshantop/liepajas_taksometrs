<?php
    $serveris = "localhost";
    $lietotajs = "root";
    $parole = "";
    $datubaze = "liepajas_taksometrs";

    $savienojums = mysqli_connect($serveris, $lietotajs, $parole, $datubaze);

    if(!$savienojums){
        die("Nav izveidots savienojums ar datubāzi: " . mysqli_connect_error());
    }else{
        die("Ir izveidots savienojums ar datubāzi"());
    }

?>