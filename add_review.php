<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Вы должны войти в аккаунт']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = trim($_POST['user_name'] ?? '');
$rating   = (int)($_POST['rating'] ?? 0);
$comment  = trim($_POST['comment'] ?? '');
$product_id = (int)($_POST['product_id'] ?? 0);

// Если авторизован — подставляем имя из БД если не указано
if (empty($user_name)) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $user_name = $user['name'];
    }
}

if (!$user_name || $rating < 1 || $rating > 5 || !$comment || !$product_id) {
    echo json_encode(['success' => false, 'error' => 'Заполните все поля корректно']);
    exit;
}

// Проверка: писал ли уже этот пользователь отзыв
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$exists = $stmt->fetch();

if ($exists) {
    echo json_encode(['success' => false, 'error' => 'Вы уже писали отзыв к этому товару']);
    exit;
}

// Если всё ок — вставляем
$stmt = $pdo->prepare("
    INSERT INTO reviews (product_id, user_id, user_name, rating, comment)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$product_id, $user_id, $user_name, $rating, $comment]);

echo json_encode(['success' => true]);
exit;