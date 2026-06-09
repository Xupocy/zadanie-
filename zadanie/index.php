<?php
session_start();
require_once 'db.php';

// Obsługa dodawania do koszyka
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $p_id = intval($_GET['id']);
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$p_id])) {
        $_SESSION['cart'][$p_id]++;
    } else {
        $_SESSION['cart'][$p_id] = 1;
    }
    header("Location: index.php");
    exit;
}

// Zmiana ilości przedmiotów w dolnym podglądzie
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'inc') {
        $_SESSION['cart'][$id]++;
    } elseif ($_GET['action'] == 'dec') {
        $_SESSION['cart'][$id]--;
        if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    } elseif ($_GET['action'] == 'remove') {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: index.php");
    exit;
}

// Obliczanie wartości koszyka do nagłówka
$cart_count = 0;
$cart_total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = mysqli_query($conn, "SELECT price FROM products WHERE id = $id");
        if ($row = mysqli_fetch_assoc($res)) {
            $cart_count += $qty;
            $cart_total += $row['price'] * $qty;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>GameStore - Sklep z grami</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container clearfix">
        <div class="header-brand">
            <a href="index.php"><h1>Game<span>Store</span></h1></a>
        </div>
        <div class="header-search">
            <form action="produkty.php" method="GET">
                <input type="text" name="search" placeholder="Szukaj gry...">
            </form>
        </div>
        <div class="header-nav">
            <a href="index.php" class="active">Strona główna</a>
            <a href="produkty.php">Produkty</a>
            <a href="koszyk.php">Koszyk</a>
            <a href="kontakt.php">Kontakt</a>
            <a href="koszyk.php" class="cart-badge-btn">Koszyk: <?php echo $cart_count; ?> | <?php echo number_format($cart_total, 2, ',', ' '); ?> zł</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/banner-main-page.png');">
        <h2>Sklep z grami</h2>
        <p>Najnowsze gry, promocje i bestsellery</p>
    </div>
</div>

<div class="container main-content clearfix">
    <aside class="sidebar-filters">
        <h3>Filtry</h3>
        <form action="produkty.php" method="GET">
            <div class="filter-box">
                <label>Gatunek</label>
                <select name="category">
                    <option value="">Wszystkie gatunki</option>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM categories");
                    while($c = mysqli_fetch_assoc($cats)) {
                        echo "<option value='{$c['id']}'>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-box">
                <label>Platforma</label>
                <select name="platform">
                    <option value="">Wszystkie platformy</option>
                    <?php
                    $plats = mysqli_query($conn, "SELECT * FROM platforms");
                    while($p = mysqli_fetch_assoc($plats)) {
                        echo "<option value='{$p['id']}'>{$p['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="game-btn">Filtrj</button>
        </form>
    </aside>

    <div class="products-display">
        <div class="products-top-bar clearfix">
            <h2>Lista produktów</h2>
        </div>

        <div class="games-grid">
            <?php
            // Pobieranie 6 pierwszych gier na główną
            $query = "SELECT p.*, c.name AS category_name, pl.name AS platform_name 
                      FROM products p
                      JOIN categories c ON p.category_id = c.id
                      JOIN platforms pl ON p.platform_id = pl.id
                      LIMIT 6";
            $result = mysqli_query($conn, $query);
            while ($game = mysqli_fetch_assoc($result)) {
            ?>
                <div class="game-item-card">
                    <img src="<?php echo $game['image']; ?>" class="game-card-img" alt="">
                    <div class="game-card-body">
                        <div class="game-card-title"><?php echo $game['title']; ?></div>
                        <div class="game-card-details">
                            Platforma: <?php echo $game['platform_name']; ?><br>
                            Gatunek: <?php echo $game['category_name']; ?>
                        </div>
                        <div class="game-card-price"><?php echo number_format($game['price'], 2, ',', ' '); ?> zł</div>
                        <a href="index.php?action=add&id=<?php echo $game['id']; ?>" class="game-btn">Dodaj do koszyka</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
<div class="container" style="margin-bottom: 40px;">
    <div style="background-color: #0a1424; border: 1px solid #1a293d; padding: 20px; border-radius: 8px;">
        <h3 style="margin-bottom: 15px; font-size: 16px;">Koszyk</h3>
        <table class="basket-table" style="margin-bottom: 0;">
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
                $subtotal = 0;
                foreach ($_SESSION['cart'] as $id => $qty) {
                    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
                    if ($game = mysqli_fetch_assoc($res)) {
                        $line_total = $game['price'] * $qty;
                        $subtotal += $line_total;
                ?>
                <tr>
                    <td>
                        <img src="<?php echo $game['image']; ?>" alt="">
                        <?php echo $game['title']; ?>
                    </td>
                    <td class="qty-control">
                        <a href="index.php?action=dec&id=<?php echo $game['id']; ?>">[-]</a>
                        <span style="margin: 0 10px;"><?php echo $qty; ?></span>
                        <a href="index.php?action=inc&id=<?php echo $game['id']; ?>">[+]</a>
                    </td>
                    <td><?php echo number_format($game['price'], 2, ',', ' '); ?> zł</td>
                    <td><?php echo number_format($line_total, 2, ',', ' '); ?> zł</td>
                    <td><a href="index.php?action=remove&id=<?php echo $game['id']; ?>" class="delete-btn">Usuń</a></td>
                </tr>
                <?php }} ?>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2" style="text-align: right; font-size: 18px; font-weight: bold;">
                        Razem: <span style="color: #38bdf8;"><?php echo number_format($subtotal, 2, ',', ' '); ?> zł</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<footer>
    <div class="container footer-cols clearfix">
        <div class="footer-col wide">
            <h4>GameStore</h4>
            <p style="color: #72849a; font-size: 13px;">Twoje miejsce na najlepsze gry. Dołącz do społeczności graczy!</p>
        </div>
        <div class="footer-col">
            <h4>Sklep</h4>
            <a href="produkty.php">Wszystkie gry</a>
            <a href="#">Promocje</a>
            <a href="#">Bestsellery</a>
        </div>
        <div class="footer-col">
            <h4>Pomoc</h4>
            <a href="#">FAQ</a>
            <a href="#">Dostawa</a>
            <a href="#">Zwroty</a>
        </div>
        <div class="footer-col">
            <h4>Informacje</h4>
            <a href="#">O nas</a>
            <a href="#">Regulamin</a>
            <a href="kontakt.php">Kontakt</a>
        </div>
    </div>
    <div class="footer-copyright">
        &copy; 2026 GameStore. Wszelkie prawa zastrzeżone.
    </div>
</footer>

</body>
</html>