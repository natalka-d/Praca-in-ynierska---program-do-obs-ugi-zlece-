<?php
$pdo = new PDO("mysql:host=localhost;dbname=phantom_login;charset=utf8", "root", "");

// lista użytkowników i ich aktualnych haseł w czystym tekście
$users = [
    1 => 'admin',
    2 => 'drukarz',
    3 => 'druk_cyfrowy',
    4 => 'druk_wielkoformatowy',
    5 => 'introligatornia',
    6 => 'laser'
];

foreach ($users as $id => $plain) {
    $hash = password_hash($plain, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $id]);

    echo "Użytkownik ID {$id} – hasło zaktualizowane<br>";
}
