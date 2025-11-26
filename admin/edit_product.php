<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('Location: ../login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die('Товар не найден');
}

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 1);
    $image = $_POST['image'] ?? '';

    if ($name && $price > 0 && $image) {
        $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE id = ?")
            ->execute([$name, $price, $stock, $category_id, $image, $id]);
        header('Location: products.php?updated=1');
        exit;
    }
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Редактировать товар</title>
    
    <link rel="stylesheet" href="../style.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <h2>Редактировать товар: <?= htmlspecialchars($product['name']) ?></h2>
    <div style="text-align: left; margin: 20px 0;"><div style="text-align: left; margin: 20px 0;">
    <a href="javascript:history.back()" style="color: #000; text-decoration: underline; font-size: 16px;">
        ← Назад
    </a>
</div>
    <form method="POST">
        <div style="margin: 15px 0;">
            <label>Название:</label><br>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required style="width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <!-- После поля "Название" -->
<div style="margin: 15px 0;">
    <label>Описание</label><br>
    <textarea name="description" rows="3" 
              style="width: 350px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
</div>
        <div style="margin: 15px 0;">
            <label>Цена (₽):</label><br>
            <input type="number" name="price" value="<?= $product['price'] ?>" required min="1" style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div style="margin: 15px 0;">
            <label>Остаток:</label><br>
            <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div style="margin: 15px 0;">
            <label>Категория:</label><br>
            <select name="category_id" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin: 15px 0;">
            <label>Имя файла изображения (в папке images):</label><br>
            <input type="text" name="image" value="<?= htmlspecialchars($product['image']) ?>" required style="width: 200px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <!-- После изображения -->
<div style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 6px;">
    <label>
        <input type="checkbox" name="is_new_collection" <?= !empty($product['is_new_collection']) ? 'checked' : '' ?>>
        В новой коллекции
    </label>
</div>
        <button type="submit" class="btn" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 4px;">Сохранить</button>
        <a href="products.php" style="margin-left: 15px; text-decoration: underline;">Отмена</a>
    </form>
</body>
</html>