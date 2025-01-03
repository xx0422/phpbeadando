<?php
session_start();
require 'db_connection.php';

if (isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute(['username' => $username, 'password' => $hashedPassword]);

        $_SESSION['username'] = $username;
        header('Location: game.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
</head>
<body>
    <h1>Regisztráció</h1>
    <form method="post">
        <label for="username">Felhasználónév:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Jelszó:</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">Regisztráció</button>
    </form>
    <p><a href="login.php">Már van fiókom</a></p>
</body>
</html>
