<?php
// db.php - Połączenie z bazą danych gamestore
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'gamestore';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

// Ustawienie kodowania znaków dla poprawnych polskich liter
mysqli_set_charset($conn, "utf8mb4");
?>