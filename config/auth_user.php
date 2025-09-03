<?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header('Location:login.php');
        exit();
    }
    // user id setup. 
    $user_id = $_SESSION['user_id'];
