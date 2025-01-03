<?php
session_start();
session_destroy();
header('Location: index.php'); // Visszairányítás a főoldalra
exit;
?>
