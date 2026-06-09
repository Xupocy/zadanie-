<?php
session_start();
require_once 'db.php';

if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $p_id = intval($_GET['id']);
    $_SESSION['cart'][$p_id] = isset($_SESSION['cart'][$p_id]) ? $_SESSION['cart'][$p_id] + 1 : 1;
    header("Location: produkty.php?" . $_SERVER['QUERY_STRING']);
    exit;
}

// Filtry
$where = "WHERE p.is_active = 1";
if (!empty($_GET['category'])) {
    $where .= " AND p.category_id = " . intval($_GET['category']);
}
if (!empty($_GET['platform'])) {
    $where .= " AND p.platform_id = " . intval($_GET['platform']);
}
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND p.title LIKE '%$search%'";
}

// Stronicowanie
$limit = 9; // 9 gier na stronę
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Liczba wszystkich pasujących produktów
$count_query = "SELECT COUNT(*) AS total FROM products p $where";
$count_res = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_res);
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $limit);

// Sortowanie
$sort_by = "p.id DESC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_asc') $sort_by = "p.price ASC";
    if ($_GET['sort'] == 'price_desc') $sort_by = "p.price DESC";
}

$query = "SELECT p.*, c.name AS category_name, pl.name AS platform_name 
          FROM products p
          JOIN categories c ON p.category_id = c.id
          JOIN platforms pl ON p.platform_id = pl.id
          $where
          ORDER BY $sort_by
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Nagłówek koszyka
$cart_count = 0; $cart_total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $res = mysqli_query($conn, "SELECT price FROM products WHERE id = $id");
        if ($row = mysqli_fetch_assoc($res)) {
            $cart_count += $qty; $cart_total += $row['price'] * $qty;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Produkty - GameStore</title>
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
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Szukaj gry...">
            </form>
        </div>
        <div class="header-nav">
            <a href="index.php">Strona główna</a>
            <a href="produkty.php" class="active">Produkty</a>
            <a href="koszyk.php">Koszyk</a>
            <a href="kontakt.php">Kontakt</a>
            <a href="koszyk.php" class="cart-badge-btn">Koszyk: <?php echo $cart_count; ?> | <?php echo number_format($cart_total, 2, ',', ' '); ?> zł</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="page-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/banner-products.png');">
        <h2>Produkty</h2>
        <p>Odkryj najlepsze gry w najlepszych cenach</p>
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
                        $sel = (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
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
                        $sel = (isset($_GET['platform']) && $_GET['platform'] == $p['id']) ? 'selected' : '';
                        echo "<option value='{$p['id']}' $sel>{$p['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="game-btn">Filtruj</button>
        </form>
    </aside>

    <div class="products-display">
        <div class="products-top-bar clearfix">
            <h2>Znaleziono <?php echo $total_products; ?> produktów</h2>
            <div class="results-info">
                <form action="produkty.php" method="GET" id="sortForm">
                    <select name="sort" onchange="this.form.submit()" style="background:#070e1b; color:white; border:1px solid #1a293d; padding:5px; border-radius:4px;">
                        <option value="">Sortuj według</option>
                        <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='price_asc')?'selected':''; ?>>Cena: od najniższej</option>
                        <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='price_desc')?'selected':''; ?>>Cena: od najwyższej</option>
                    </select>
                    <?php if(!empty($_GET['category'])): ?><input type="hidden" name="category" value="<?php echo intval($_GET['category']); ?>"><?php endif; ?>
                    <?php if(!empty($_GET['platform'])): ?><input type="hidden" name="platform" value="<?php echo intval($_GET['platform']); ?>"><?php endif; ?>
                    <?php if(!empty($_GET['search'])): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>"><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="games-grid">
            <?php while ($game = mysqli_fetch_assoc($result)) { ?>
                <div class="game-item-card">
                    <img src="<?php echo $game['image']; ?>" class="game-card-img" alt="">
                    <div class="game-card-body">
                        <div class="game-card-title"><?php echo $game['title']; ?></div>
                        <div class="game-card-details">
                            Platforma: <?php echo $game['platform_name']; ?><br>
                            Gatunek: <?php echo $game['category_name']; ?>
                        </div>
                        <div class="game-card-price"><?php echo number_format($game['price'], 2, ',', ' '); ?> zł</div>
                        <a href="produkty.php?<?php echo $_SERVER['QUERY_STRING']; ?>&action=add&id=<?php echo $game['id']; ?>" class="game-btn">Dodaj do koszyka</a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$total_pages; $i++): 
                $params = $_GET; $params['page'] = $i; $url_params = http_build_query($params);
            ?>
                <a href="produkty.php?<?php echo $url_params; ?>" class="<?php echo ($page == $i)?'active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <div class="container footer-copyright">&copy; 2026 GameStore. Wszelkie prawa zastrzeżone.</div>
</footer>

</body>
</html>