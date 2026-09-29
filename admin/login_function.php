<?php
    session_start();
    require "../database/db_config.php";

    if(isset($_POST["ielogoties"])){
        $lietotajvards = $_POST["lietotajs"];
        $parole = $_POST["parole"];

        $sql = $savienojums->prepare("SELECT lietotajvards, parole FROM darbinieki 
        WHERE lietotajvards = ? AND statuss = 'aktīvs' LIMIT 1");
        $sql->bind_param("s", $lietotajvards);
        $sql->execute();

        $rezultats = $sql->get_result();
        if($rezultats->num_rows === 1){
            $lietotajs = $rezultats->fetch_assoc();
            if(password_verify($parole, $lietotajs["parole"])){
                $_SESSION["lietotajvards_GSHT"] = $lietotajs["lietotajvards"];
                header("Location: ./");
                exit;
            }else if(!password_verify($parole, $lietotajs["parole"])){
                $_SESSION["pazinojums"] = "Nepareizs lietotājvards vai parole!";
                header("Location: login.php");
            }else{
                $_SESSION["pazinojums"] = "Gaidiet apstiprinajumu";
                header("Location: ./");
            }
        }
        
    }

?>