<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('Location: ../login.php');
    exit;
}

// Удаление товара
if ($_GET['action'] ?? null === 'delete') {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['id']]);
    header('Location: products.php');
    exit;
}

// Получение товаров
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Товары — Админка</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .admin-container {
            max-width: 1300px;
            margin: 0 auto;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            font-size: 16px;
            color: #333;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        .btn-add {
            display: inline-block;
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .btn-add:hover {
            background: #45a047;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        th {
            background: #f4f4f4;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #fafafa;
        }

        img.product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
        }

        .action-links a {
            margin-right: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .action-edit {
            color: #0077cc;
        }

        .action-edit:hover {
            text-decoration: underline;
        }

        .action-delete {
            color: #d00;
        }

        .action-delete:hover {
            text-decoration: underline;
        }
    </style>

</head>
<body>

<div class="admin-container">

    <h2>Товары</h2>

    <a href="index.php" style="color: #000; text-decoration: underline; font-size: 16px;">
    ← В админ-панель
</a>

    <p><a href="add_product.php" class="btn-add">+ Добавить товар</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Фото</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Остаток</th>
                <th>Категория</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>

                    <td>
                        <img class="product-img" src="../images/<?= htmlspecialchars($p['image']) ?>">
                    </td>

                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
                    <td><?= $p['stock'] ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>

                    <td class="action-links">
                        <a class="action-edit" href="edit_product.php?id=<?= $p['id'] ?>">Редактировать</a>
                        <a class="action-delete" href="?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('Удалить товар?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
