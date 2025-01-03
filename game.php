<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Kérdezzük le a felhasználó statisztikáit
$stmt = $pdo->prepare("SELECT * FROM statistics WHERE username = :username");
$stmt->execute(['username' => $_SESSION['username']]);
$userStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Ha a statisztikák még nem léteznek, létrehozzuk őket
if (!$userStats) {
    $stmt = $pdo->prepare("INSERT INTO statistics (username) VALUES (:username)");
    $stmt->execute(['username' => $_SESSION['username']]);
    $userStats = ['correct_guesses' => 0, 'incorrect_guesses' => 0];
}

// Játék inicializálása
if (!isset($_SESSION['game'])) {
    $_SESSION['game'] = [
        'word' => '',
        'guessed' => [],
        'attempts' => 6,
        'status' => '' // Hozzáadjuk a 'status' alapértelmezett értéket
    ];

    $stmt = $pdo->query("SELECT word FROM words ORDER BY RAND() LIMIT 1");
    $word = $stmt->fetch(PDO::FETCH_ASSOC)['word'] ?? 'example';
    $_SESSION['game']['word'] = strtolower($word);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guess'])) {
        // A felhasználó tippelése
        $guess = strtolower(trim($_POST['guess'] ?? ''));

        if (strlen($guess) === 1 && ctype_alpha($guess)) {
            if (!in_array($guess, $_SESSION['game']['guessed'])) {
                $_SESSION['game']['guessed'][] = $guess;

                if (!str_contains($_SESSION['game']['word'], $guess)) {
                    $_SESSION['game']['attempts']--;
                }
            }
        }

        // Játék állapot ellenőrzése
        $wordArray = str_split($_SESSION['game']['word']);
        $guessedCorrectly = array_intersect($wordArray, $_SESSION['game']['guessed']);
        if (count(array_diff($wordArray, $guessedCorrectly)) === 0) {
            // Ha a felhasználó kitalálta a szót
            $stmt = $pdo->prepare("UPDATE statistics SET correct_guesses = correct_guesses + 1 WHERE username = :username");
            $stmt->execute(['username' => $_SESSION['username']]);
            $_SESSION['game']['status'] = 'won';
        } elseif ($_SESSION['game']['attempts'] <= 0) {
            // Ha a felhasználó elvesztette
            $stmt = $pdo->prepare("UPDATE statistics SET incorrect_guesses = incorrect_guesses + 1 WHERE username = :username");
            $stmt->execute(['username' => $_SESSION['username']]);
            $_SESSION['game']['status'] = 'lost';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akasztófa játék</title>
</head>
<body>
    <h1>Akasztófa Játék</h1>

    <p>Üdvözlünk, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    <p>Talált szavak: <?= $userStats['correct_guesses'] ?> | Nem talált szavak: <?= $userStats['incorrect_guesses'] ?></p>

    <p>Találgatott betűk: <?= implode(', ', $_SESSION['game']['guessed'] ?? []) ?></p>
    <p>Maradék próbálkozások: <?= $_SESSION['game']['attempts'] ?></p>

    <p>
        <?php
        foreach (str_split($_SESSION['game']['word']) as $char) {
            echo in_array($char, $_SESSION['game']['guessed'] ?? []) ? $char : '_';
            echo ' ';
        }
        ?>
    </p>

    <form method="post">
        <label for="guess">Találgatás:</label>
        <input type="text" id="guess" name="guess" maxlength="1" required>
        <button type="submit">Küldés</button>
    </form>

    <?php if (isset($_SESSION['game']['status']) && $_SESSION['game']['status']): ?>
        <h2>Játék vége</h2>
        <?php if ($_SESSION['game']['status'] === 'won'): ?>
            <p>Gratulálunk! Kitaláltad a szót: <?= $_SESSION['game']['word'] ?></p>
        <?php elseif ($_SESSION['game']['status'] === 'lost'): ?>
            <p>Vesztettél! A helyes szó: <?= $_SESSION['game']['word'] ?></p>
        <?php endif; ?>
        <form method="post" action="game.php">
            <button type="submit" name="new_word">Új szó kérés</button>
        </form>
        <?php
        // Ha az új szó kérése történt, új játékot indítunk
        if (isset($_POST['new_word'])) {
            // Játék adatok törlése
            unset($_SESSION['game']);
            header('Location: game.php');
            exit;
        }
        ?>
    <?php endif; ?>

    <p><a href="logout.php">Kijelentkezés</a></p>
</body>
</html>
