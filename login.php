<?php
session_start();
require 'db_connection.php';

if (isset($_SESSION['username'])) {
    header('Location: game.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header('Location: game.php');
            exit;
        } else {
            $error = "Hibás felhasználónév vagy jelszó!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
</head>
<body>
    <h1>Bejelentkezés</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="username">Felhasználónév:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Jelszó:</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">Bejelentkezés</button>
    </form>
    <p><a href="register.php">Regisztrálj itt</a></p>
</body>
</html>
