<?php
require_once 'header.php';

// Параметры
$sort = $_GET['sort'] ?? 'name';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Подготовка запроса
$sql = "
    SELECT sp.*, p.name, p.image, p.category_id, c.name as category_name
    FROM sale_products sp
    JOIN products p ON sp.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE 1=1
";

$params = [];

// Фильтр по цене
if ($min_price !== '') {
    $sql .= " AND sp.new_price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price !== '') {
    $sql .= " AND sp.new_price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Сортировка
$allowed_sort = [
    'name' => 'p.name ASC',
    'price_asc' => 'sp.new_price ASC',
    'price_desc' => 'sp.new_price DESC',
    'created_at' => 'p.id DESC'
];
$orderBy = $allowed_sort[$sort] ?? 'p.id DESC';
$sql .= " ORDER BY " . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sale_products = $stmt->fetchAll();
?>

<main class="container" style="margin: 50px auto; max-width: 1200px;">
    <h2 style="text-align: center; margin-bottom: 40px; color: #ff4d4d;">Распродажа скидки до 50 %</h2>

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

    <?php if (empty($sale_products)): ?>
        <p style="text-align: center; font-size: 18px; color: #666;">
            Пока нет товаров со скидкой.
        </p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
            <?php foreach ($sale_products as $item): ?>
                <a href="product.php?id=<?= $item['product_id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                    <div class="product-card" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column;">
                        <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             style="width: 100%; height: 300px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                        <div style="padding: 20px; text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between; align-items: center; min-height: 200px;">
                            <div style="text-align: center; margin-bottom: 10px; min-height: 60px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <h3 style="font-size: 18px; margin: 0;"><?= htmlspecialchars($item['name']) ?></h3>
                            </div>

                            <div style="text-align: center; margin-bottom: 10px;">
                                <div style="margin: 5px 0; font-size: 16px; color: #888; text-decoration: line-through;">
                                    <?= number_format($item['old_price'], 0, '', ' ') ?> ₽
                                </div>
                                <div style="font-size: 20px; font-weight: 700; color: #ff0000;">
                                    <?= number_format($item['new_price'], 0, '', ' ') ?> ₽
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>