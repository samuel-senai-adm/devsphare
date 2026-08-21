<?php

// produção
$host = "localhost";
$user = "devsphar_adm";
$password = "vP[uA_ljYzUVyx)s";
$database = "devsphar_devsphar_smartlocker";

// local
// $host = "10.140.170.57";
// $user = "root";
// $password = "123456";
// $database = "smartlocker";
// $port = "3307";
//akira

//samuel

// produção
$conn = new mysqli($host, $user, $password, $database);

// local
// $conn = new mysqli($host, $user, $password, $database, $port);

if($conn->connect_error){
    die("Erro na conexão");
}

?>