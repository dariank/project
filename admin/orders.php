<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('HTTP/1.0 403 Forbidden');
    die('Доступ запрещён');
}

// Получаем параметры фильтрации
$user_id = $_GET['user_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';
// Обработка изменения статуса заказа
if ($_POST['order_id'] ?? null) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'] ?? 'Обрабатывается';

    // Защита от неверных значений
    $allowed_statuses = ['Обрабатывается', 'В пути', 'Доставлен', 'Отменён'];
    if (!in_array($status, $allowed_statuses)) {
        $status = 'Обрабатывается';
    }

    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
    
    // Перезагружаем с теми же фильтрами
    $redirect = 'orders.php?' . http_build_query($_GET);
    header('Location: ' . $redirect);
    exit;
}
// Формируем SQL-запрос
$sql = "
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";
$params = [];

// Фильтр по пользователю
if ($user_id) {
    $sql .= " AND o.user_id = ?";
    $params[] = $user_id;
}

// Фильтр по дате ОТ
if ($date_from) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

// Фильтр по дате ДО
if ($date_to) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

// Фильтр по статусу
if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Список всех пользователей для фильтра
$users = $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Заказы — Админка</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <h2>Заказы</h2>
    <div style="text-align: left; margin: 20px 0;"><div style="text-align: left; margin: 20px 0;">
    <a href="index.php" style="color: #000; text-decoration: underline; font-size: 16px;">
    ← В админ-панель
</a>
</div>

    <!-- Форма фильтрации -->
    <form method="GET" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: end;">
            <div>
                <label>Пользователь:</label><br>
                <select name="user_id" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; min-width: 180px;">
                    <option value="">Все</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $user_id == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Дата от:</label><br>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"
                       style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div>
                <label>Дата до:</label><br>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"
                       style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div>
                <label>Статус:</label><br>
                <select name="status" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; min-width: 150px;">
                    <option value="">Все</option>
                    <option value="Обрабатывается" <?= $status === 'Обрабатывается' ? 'selected' : '' ?>>Обрабатывается</option>
                    <option value="В пути" <?= $status === 'В пути' ? 'selected' : '' ?>>В пути</option>
                    <option value="Доставлен" <?= $status === 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                    <option value="Отменён" <?= $status === 'Отменён' ? 'selected' : '' ?>>Отменён</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn" style="padding: 8px 16px;">Применить</button>
                <a href="orders.php" style="margin-left: 10px; color: #000; text-decoration: underline;">Сбросить</a>
            </div>
        </div>
    </form>

    <!-- Список заказов -->
    <?php if (empty($orders)): ?>
        <p>Заказы не найдены.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div style="border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 25px; background: #fafafa;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Заказ #<?= $order['id'] ?> от <?= htmlspecialchars($order['user_name']) ?></h3>
                    <div><strong><?= number_format($order['total'], 0, '', ' ') ?> ₽</strong></div>
                </div>
                <p><strong>Дата:</strong> <?= $order['created_at'] ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['user_email']) ?></p>

                <!-- Статус -->
                <form method="POST" style="margin-top: 15px; display: inline-block;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Обрабатывается" <?= $order['status'] === 'Обрабатывается' ? 'selected' : '' ?>>Обрабатывается</option>
                        <option value="В пути" <?= $order['status'] === 'В пути' ? 'selected' : '' ?>>В пути</option>
                        <option value="Доставлен" <?= $order['status'] === 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                        <option value="Отменён" <?= $order['status'] === 'Отменён' ? 'selected' : '' ?>>Отменён</option>
                    </select>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>