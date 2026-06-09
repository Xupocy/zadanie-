<?php
session_start();
require_once 'db.php';

// Zmiana ilości lub usuwanie
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'inc') $_SESSION['cart'][$id]++;
    if ($_GET['action'] == 'dec') {
        $_SESSION['cart'][$id]--;
        if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    }
    if ($_GET['action'] == 'remove') unset($_SESSION['cart'][$id]);
    header("Location: koszyk.php");
    exit;
}

// Obsługa kuponu rabatowego
$discount = 0;
$code_id = "NULL";
if (isset($_POST['coupon_code'])) {
    $code = mysqli_real_escape_string($conn, $_POST['coupon_code']);
    if ($code === 'GAME10') {
        $_SESSION['discount_percent'] = 10;
        $_SESSION['coupon_code_str'] = 'GAME10';
    }
}

// Czyszczenie koszyka
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    unset($_SESSION['cart']);
    unset($_SESSION['discount_percent']);
    unset($_SESSION['coupon_code_str']);
    header("Location: koszyk.php");
    exit;
}

// Przetwarzanie wysłania zamówienia
if (isset($_POST['place_order'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $total_before = floatval($_POST['total_before']);
    $disc_amount = floatval($_POST['disc_amount']);
    $total_after = floatval($_POST['total_after']);
    
    $ins_order = "INSERT INTO orders (full_name, email, phone, address, payment_method, delivery_method, total_amount_before_discount, discount_amount, total_amount) 
                  VALUES ('$name', '$email', '$phone', '$address', 'Karta / Przelew', 'Kurier', $total_before, $disc_amount, $total_after)";
    
    if (mysqli_query($conn, $ins_order)) {
        $order_id = mysqli_insert_id($conn);
        foreach ($_SESSION['cart'] as $id => $qty) {
            $p_res = mysqli_query($conn, "SELECT price FROM products WHERE id = $id");
            $p_row = mysqli_fetch_assoc($p_res);
            $u_price = $p_row['price'];
            $l_total = $u_price * $qty;
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES ($order_id, $id, $qty, $u_price, $l_total)");
        }
        unset($_SESSION['cart']);
        unset($_SESSION['discount_percent']);
        echo "<script>alert('Zamówienie złożone pomyślnie!'); window.location.href='index.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszyk - GameStore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container clearfix">
        <div class="header-brand"><a href="index.php"><h1>Game<span>Store</span></h1></a></div>
        <div class="header-nav">
            <a href="index.php">Strona główna</a>
            <a href="produkty.php">Produkty</a>
            <a href="koszyk.php" class="active">Koszyk</a>
            <a href="kontakt.php">Kontakt</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/banner-basket.png');">
        <h2>Koszyk</h2>
        <p>Sprawdź zawartość koszyka i przejdź do zamówienia</p>
    </div>
</div>

<div class="container main-content clearfix">
    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
        <p style="text-align: center; padding: 40px; color: #72849a;">Twój koszyk jest pusty.</p>
    <?php else: ?>
        <div class="left-main-column">
            <table class="basket-table">
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Ilość</th>
                        <th>Cena jednostkowa</th>
                        <th>Suma</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_before = 0;
                    foreach ($_SESSION['cart'] as $id => $qty) {
                        $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
                        if ($game = mysqli_fetch_assoc($res)) {
                            $line_total = $game['price'] * $qty;
                            $total_before += $line_total;
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $game['image']; ?>" alt="">
                            <?php echo $game['title']; ?>
                        </td>
                        <td class="qty-control">
                            <a href="koszyk.php?action=dec&id=<?php echo $game['id']; ?>">-</a>
                            <span style="margin: 0 10px;"><?php echo $qty; ?></span>
                            <a href="koszyk.php?action=inc&id=<?php echo $game['id']; ?>">+</a>
                        </td>
                        <td><?php echo number_format($game['price'], 2, ',', ' '); ?> zł</td>
                        <td><?php echo number_format($line_total, 2, ',', ' '); ?> zł</td>
                        <td><a href="koszyk.php?action=remove&id=<?php echo $game['id']; ?>" class="delete-btn">Usuń</a></td>
                    </tr>
                    <?php }} ?>
                </tbody>
            </table>
            <a href="koszyk.php?action=clear" class="game-btn" style="width: 150px; background-color: #ef4444;">Wyczyść koszyk</a>
        </div>

        <div class="right-sidebar-column">
            <div class="sidebar-summary-box">
                <h3>Podsumowanie zamówienia</h3>
                <div class="summary-row clearfix">
                    <span class="left">Wartość produktów</span>
                    <span class="right"><?php echo number_format($total_before, 2, ',', ' '); ?> zł</span>
                </div>
                <div class="summary-row clearfix">
                    <span class="left">Dostawa</span>
                    <span class="right" style="color: #22c55e;">Darmowa dostawa</span>
                </div>
                
                <?php 
                $disc_percent = isset($_SESSION['discount_percent']) ? $_SESSION['discount_percent'] : 0;
                $disc_amount = ($total_before * $disc_percent) / 100;
                $total_after = $total_before - $disc_amount;
                if ($disc_percent > 0):
                ?>
                <div class="summary-row clearfix" style="color: #ef4444;">
                    <span class="left">Rabat (<?php echo $_SESSION['coupon_code_str']; ?>)</span>
                    <span class="right">-<?php echo number_format($disc_amount, 2, ',', ' '); ?> zł</span>
                </div>
                <?php endif; ?>

                <div class="summary-row total-border clearfix">
                    <span class="left">Razem</span>
                    <span class="right"><?php echo number_format($total_after, 2, ',', ' '); ?> zł</span>
                </div>

                <form action="koszyk.php" method="POST" style="margin-top: 20px;" class="clearfix">
                    <input type="text" name="coupon_code" class="coupon-input" placeholder="Kod rabatowy">
                    <button type="submit" class="coupon-btn">Zastosuj</button>
                </form>
            </div>

            <div class="contact-card-box">
                <h3>Dane do wysyłki</h3>
                <form action="koszyk.php" method="POST">
                    <input type="hidden" name="total_before" value="<?php echo $total_before; ?>">
                    <input type="hidden" name="disc_amount" value="<?php echo $disc_amount; ?>">
                    <input type="hidden" name="total_after" value="<?php echo $total_after; ?>">
                    
                    <label style="font-size:12px; color:#72849a;">Imię i Nazwisko</label>
                    <input type="text" name="full_name" class="form-field" required>
                    
                    <label style="font-size:12px; color:#72849a;">E-mail</label>
                    <input type="email" name="email" class="form-field" required>
                    
                    <label style="font-size:12px; color:#72849a;">Telefon</label>
                    <input type="text" name="phone" class="form-field" required>
                    
                    <label style="font-size:12px; color:#72849a;">Adres dostawy</label>
                    <textarea name="address" class="form-field" style="height: 60px;" required></textarea>
                    
                    <button type="submit" name="place_order" class="game-btn purple-btn" style="padding:12px;">Przejdź do zamówienia</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>