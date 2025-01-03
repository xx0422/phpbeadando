<?php

$host = 'sql301.infinityfree.com'; 
$db = 'if0_38033744_hangman_game'; 
$user = 'if0_38033744'; 
$password = 'Yutrfokaj568277'; 
$dsn = "mysql:host=$host;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Adatbázis kapcsolat sikertelen: " . $e->getMessage());
}
?>

