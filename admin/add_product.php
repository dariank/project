<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('HTTP/1.0 403 Forbidden');
    die('Доступ запрещён');
}

$message = '';

// Загружаем категории, цвета, размеры
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$colors = $pdo->query("SELECT * FROM colors ORDER BY name")->fetchAll();
$sizes = $pdo->query("SELECT * FROM sizes ORDER BY name")->fetchAll();

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 100);
    $category_id = (int)($_POST['category_id'] ?? 1);
    $image = trim($_POST['image'] ?? '');
    $is_new_collection = isset($_POST['is_new_collection']) ? 1 : 0;
    $selected_color = (int)($_POST['color_id'] ?? 0);

    if ($name && $price > 0 && $image && $selected_color) {
        // 1. Добавляем товар
        $pdo->prepare("
            INSERT INTO products (name, description, price, stock, category_id, image, is_new_collection, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ")->execute([$name, $description, $price, $stock, $category_id, $image, $is_new_collection]);

        // 2. Получаем ID нового товара
        $product_id = $pdo->lastInsertId();

        // 3. Добавляем ВСЕ размеры для выбранного цвета
        foreach ($sizes as $size) {
            $pdo->prepare("INSERT INTO product_variants (product_id, color_id, size_id, stock) VALUES (?, ?, ?, ?)")
                ->execute([$product_id, $selected_color, $size['id'], $stock]);
        }

        header('Location: products.php?created=1');
        exit;
    } else {
        $message = 'Заполните все обязательные поля (название, цена, фото, цвет).';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Добавить товар — Админка</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <h2>Добавить новый товар</h2>

    <?php if ($message): ?>
        <div style="color: red; background: #ffe6e6; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin: 15px 0;">
            <label>Название *</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required 
                   style="width: 350px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin: 15px 0;">
            <label>Описание</label><br>
            <textarea name="description" rows="3" 
                      style="width: 350px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div style="margin: 15px 0;">
            <label>Цена (₽) *</label><br>
            <input type="number" name="price" value="<?= (int)($_POST['price'] ?? 0) ?>" required min="1"
                   style="width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin: 15px 0;">
            <label>Остаток</label><br>
            <input type="number" name="stock" value="<?= (int)($_POST['stock'] ?? 100) ?>" min="0"
                   style="width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin: 15px 0;">
            <label>Категория *</label><br>
            <select name="category_id" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 200px;">
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == ($_POST['category_id'] ?? 1)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin: 15px 0;">
            <label>Цвет *</label><br>
            <select name="color_id" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 200px;">
                <option value="">Выберите цвет</option>
                <?php foreach ($colors as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($c['id'] == ($_POST['color_id'] ?? 0)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

   <!-- Размеры больше не выбираются — добавляются все автоматически -->
        <div style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 6px; font-size: 14px; color: #666;">
            ✅ ВСЕ размеры (S, M, L, XL...) будут добавлены автоматически для выбранного цвета.
        </div>

        <div style="margin: 15px 0;">
            <label>Имя файла изображения (в папке images) *</label><br>
            <input type="text" name="image" value="<?= htmlspecialchars($_POST['image'] ?? '') ?>" required
                   style="width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                   placeholder="dress1.jpg">
        </div>

        <div style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 6px;">
            <label>
                <input type="checkbox" name="is_new_collection" <?= !empty($_POST['is_new_collection']) ? 'checked' : '' ?>>
                Добавить в «Новую коллекцию»
            </label>
        </div>

        <button type="submit" class="btn" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 4px;">
            Создать товар
        </button>
        <a href="products.php" style="margin-left: 15px; text-decoration: underline;">Отмена</a>
    </form>
    <div style="text-align: left; margin: 20px 0;">
    <a href="javascript:history.back()" style="color: #000; text-decoration: underline; font-size: 16px;">
        ← Назад
    </a>
</div>
</body>
</html>