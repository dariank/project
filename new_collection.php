<?php
require_once 'header.php';

// Получаем параметры
$sort = $_GET['sort'] ?? 'name';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Готовим SQL
$sql = "SELECT id, name, price, image FROM products WHERE is_new_collection = 1";
$params = [];

// Фильтр по цене
if ($min_price !== '') {
    $sql .= " AND price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price !== '') {
    $sql .= " AND price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Сортировка
$allowed_sort = [
    'name' => 'name ASC',
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'created_at' => 'id DESC'
];
$orderBy = $allowed_sort[$sort] ?? 'id DESC';
$sql .= " ORDER BY " . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<main class="container" style="margin: 50px auto; max-width: 1200px;">
    <h2 style="text-align: center; margin-bottom: 40px;">Новая коллекция 2025</h2>

    <!-- Фильтры и сортировка -->
    <form method="GET" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; margin-bottom: 30px;">
        <label>
            Цена от:
            <input type="number" name="min_price" value="<?= htmlspecialchars($min_price) ?>"
                   onchange="this.form.submit()"
                   style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100px;" min="0">
        </label>
        <label>
            до:
            <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>"
                   onchange="this.form.submit()"
                   style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100px;" min="0">
        </label>
        <label>
            Сортировка:
            <select name="sort" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>По названию</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>По цене (возр.)</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>По цене (убыв.)</option>
                <option value="created_at" <?= $sort == 'created_at' ? 'selected' : '' ?>>Новизне</option>
            </select>
        </label>
    </form>

    <!-- Товары -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
        <?php foreach ($products as $p): ?>
            <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s;">
                    <img src="images/<?= htmlspecialchars($p['image']) ?>" 
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         style="width: 100%; height: 300px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                    <div style="padding: 20px; text-align: center;">
                        <h3 style="font-size: 18px; margin: 10px 0;"><?= htmlspecialchars($p['name']) ?></h3>
                        <div style="font-size: 20px; font-weight: 700; color: #000;">
                            <?= number_format($p['price'], 0, '', ' ') ?> ₽
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>