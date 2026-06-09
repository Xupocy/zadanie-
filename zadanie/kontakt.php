<?php
session_start();
require_once 'db.php';

if (isset($_POST['send_msg'])) {
    // Prosta symulacja odebrania wiadomości w PHP
    echo "<script>alert('Dziękujemy! Wiadomość została wysłana.'); window.location.href='kontakt.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kontakt - GameStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container clearfix">
        <div class="header-brand"><a href="index.php"><h1>Game<span>Store</span></h1></a></div>
        <div class="header-nav">
            <a href="index.php">Strona główna</a>
            <a href="produkty.php">Produkty</a>
            <a href="koszyk.php">Koszyk</a>
            <a href="kontakt.php" class="active">Kontakt</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/banner-contact.png');">
        <h2>Kontakt</h2>
        <p>Skontaktuj się z nami</p>
    </div>
</div>

<div class="container main-content clearfix">
    <div class="left-main-column">
        <div class="contact-card-box">
            <h3>Napisz do nas</h3>
            <p style="color: #72849a; margin-bottom: 15px;">Masz pytanie, potrzebujesz pomocy lub chcesz zgłosić sugestię? Wypełnij formularz.</p>
            
            <form action="kontakt.php" method="POST">
                <label style="color:#72849a; font-size:12px;">Imię</label>
                <input type="text" class="form-field" placeholder="Wpisz swoje imię" required>
                
                <label style="color:#72849a; font-size:12px;">E-mail</label>
                <input type="email" class="form-field" placeholder="Wpisz adres e-mail" required>
                
                <label style="color:#72849a; font-size:12px;">Temat</label>
                <select class="form-field">
                    <option>Wybierz temat wiadomości</option>
                    <option>Obsługa zamówienia</option>
                    <option>Pomoc techniczna</option>
                </select>
                
                <label style="color:#72849a; font-size:12px;">Wiadomość</label>
                <textarea class="form-field" style="height: 120px;" placeholder="Napisz swoją wiadomość..." required></textarea>
                
                <button type="submit" name="send_msg" class="game-btn purple-btn" style="width: 200px;">Wyślij wiadomość</button>
            </form>
        </div>
    </div>

    <div class="right-sidebar-column">
        <div class="contact-card-box">
            <h3>Dane kontaktowe</h3>
            
            <div class="contact-info-item">
                <strong>Adres</strong>
                <p style="color: #72849a;">GameStore Sp. z o.o.<br>ul. Gracza 12<br>00-001 Warszawa, Polska</p>
            </div>
            
            <div class="contact-info-item">
                <strong>Telefon</strong>
                <p style="color: #72849a;">+48 123 456 789<br>Pon. - Pt.: 9:00 - 18:00</p>
            </div>
            
            <div class="contact-info-item">
                <strong>E-mail</strong>
                <p style="color: #72849a;">kontakt@gamestore.pl<br>Odpowiadamy w ciągu 24h</p>
            </div>
            
            <div class="contact-info-item">
                <strong>Znajdź nas w sieci</strong>
                <div class="social-icons" style="margin-top: 10px;">
                    <a href="#">D</a>
                    <a href="#">F</a>
                    <a href="#">I</a>
                    <a href="#">Y</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>