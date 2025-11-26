<?php
require_once 'header.php';

// Получаем параметры из GET
$sort = $_GET['sort'] ?? 'name';
$category_name = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Если передана категория — получаем её ID
$category_id = null;
if ($category_name) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$category_name]);
    $cat = $stmt->fetch();
    if ($cat) {
        $category_id = $cat['id'];
    }
}

// Подготавливаем SQL запрос
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($category_id) {
    $sql .= " AND p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}
if ($min_price !== '') {
    $sql .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
}
if ($max_price !== '') {
    $sql .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Сортировка
$allowed_sort = [
    'name' => 'p.name ASC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'created_at' => 'p.created_at DESC'
];

if (isset($allowed_sort[$sort])) {
    $sql .= " ORDER BY " . $allowed_sort[$sort];
} else {
    $sql .= " ORDER BY p.name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<main class="container">
    <h2>Каталог одежды</h2>

    <!-- Фильтры -->
    <form method="GET" id="filterForm" class="filters">
        <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; margin-bottom: 30px;">
            <label>
                Категория:
                <select name="category" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Все категории</option>
                    <?php
                    $cats = $pdo->query("SELECT * FROM categories")->fetchAll();
                    foreach ($cats as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $category_name == $cat['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

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
        </div>
    </form>

  <!-- Сетка товаров -->
<div class="products-grid">
    <?php if (empty($products)): ?>
        <p>Товары не найдены.</p>
    <?php else: ?>
       <?php foreach ($products as $p): ?>
    <a href="product.php?id=<?= $p['id'] ?>" 
       style="text-decoration: none; color: inherit; display: block;">
        <div class="product-card" 
             style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column;
                    <?= $p['stock'] == 0 ? 'filter: grayscale(100%); opacity: 0.7;' : '' ?>">

            <img src="images/<?= htmlspecialchars($p['image']) ?>" 
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 style="width: 100%; height: 300px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

            <div style="padding: 20px; text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between; align-items: center; min-height: 200px;">
                <div style="text-align: center; margin-bottom: 10px;">
                    <h3 style="font-size: 18px; margin: 0;"><?= htmlspecialchars($p['name']) ?></h3>
                    <div style="margin: 10px 0; font-size: 14px; color: #666;"><?= htmlspecialchars($p['category_name']) ?></div>
                </div>

                <div style="text-align: center; margin-bottom: 10px;">
                    <?php if ($p['old_price'] && $p['old_price'] > $p['price']): ?>
                        <div style="margin: 5px 0; font-size: 16px; color: #888; text-decoration: line-through;">
                            <?= number_format($p['old_price'], 0, '', ' ') ?> ₽
                        </div>
                        <div style="font-size: 20px; font-weight: 700; color: #ff0000;">
                            <?= number_format($p['price'], 0, '', ' ') ?> ₽
                        </div>
                    <?php else: ?>
                        <div style="font-size: 20px; font-weight: 700; color: #000;">
                            <?= number_format($p['price'], 0, '', ' ') ?> ₽
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($p['stock'] == 0): ?>
                   
                <?php endif; ?>
            </div>
        </div>
    </a>
<?php endforeach; ?>
    <?php endif; ?>
</div>