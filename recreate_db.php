<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'lgu_document_tracking';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Dropping database $db...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$db` ");
    echo "Database dropped.\n";

    echo "Creating database $db...\n";
    $pdo->exec("CREATE DATABASE `$db` ");
    echo "Database created.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
