<?php
session_start();
require_once '../db.php';

// Проверка: только админ
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('Location: ../login.php');
    exit;
}

// Удаление отзыва
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: admin_reviews.php");

    exit;
}

// Получение всех отзывов
$reviews = $pdo->query("
    SELECT r.*, u.name AS user_name, p.name AS product_name 
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    ORDER BY r.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Отзывы — Админка</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #222;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #f7f7f7;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .delete-btn {
            color: #d00;
            text-decoration: none;
            font-weight: bold;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .back-link {
            color: #000;
            text-decoration: underline;
            font-size: 16px;
        }
    </style>
</head>
<body>

<h2>Отзывы</h2>

<a href="index.php" style="color: #000; text-decoration: underline; font-size: 16px;">
    ← В админ-панель
</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Товар</th>
            <th>Оценка</th>
            <th>Текст</th>
            <th>Дата</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reviews as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['user_name']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= $r['rating'] ?> ★</td>
                <td><?= htmlspecialchars($r['comment']) ?>
</td>
                <td><?= $r['created_at'] ?></td>
                <td>
                    <a class="delete-btn" 
                       href="?action=delete&id=<?= $r['id'] ?>"
                       onclick="return confirm('Удалить отзыв?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
