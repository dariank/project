<?php
session_start();
require_once 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Получаем заказы
$orders = [];
$stmt = $pdo->prepare("
    SELECT id, total, status, created_at 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Обработка обновления профиля
$update_message = '';
if ($_POST['action'] ?? null === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$name || !$email) {
        $update_message = 'Имя и email обязательны.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_message = 'Некорректный email.';
    } else {
        // Проверяем, не занят ли email другим пользователем
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $update_message = 'Этот email уже используется.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);
            $_SESSION['user_name'] = $name;
            $user['name'] = $name;
            $user['email'] = $email;
            $update_message = 'Данные успешно обновлены!';
        }
    }
}
?>

<?php require_once 'header.php'; ?>

<main class="container" style="margin: 50px auto; max-width: 800px;">
    <h2 style="text-align: center; margin-bottom: 30px;">Личный кабинет</h2>

    <!-- Вкладки: имитируем через якоря -->
    <div style="display: flex; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <a href="#profile" style="text-decoration: none; color: #000; font-weight: 600;">Профиль</a>
        <a href="#orders" style="text-decoration: none; color: #888;">Мои заказы</a>
        <a href="logout.php" style="margin-left: auto; color: #ff4d4d; text-decoration: none;">Выйти</a>
    </div>

    <!-- Профиль -->
    <section id="profile">
        <h3>Личные данные</h3>
        <?php if ($update_message): ?>
            <div style="background: <?= strpos($update_message, 'успешно') !== false ? '#d4edda' : '#f8d7da'; ?>; 
                        color: <?= strpos($update_message, 'успешно') !== false ? '#155724' : '#721c24'; ?>; 
                        padding: 12px; border-radius: 4px; margin: 15px 0;">
                <?= htmlspecialchars($update_message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" style="display: flex; flex-direction: column; gap: 16px; margin-top: 20px;">
            <input type="hidden" name="action" value="update_profile">
            <div>
                <label>Имя</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <button type="submit" class="btn" style="width: fit-content;">Сохранить изменения</button>
        </form>
    </section>

    <!-- Заказы -->
    <section id="orders" style="margin-top: 60px;">
        <h3>Мои заказы</h3>
        <?php if (empty($orders)): ?>
            <p style="color: #666; margin-top: 20px;">У вас пока нет заказов.</p>
        <?php else: ?>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($orders as $order): ?>
                    <div style="border: 1px solid #eee; padding: 20px; border-radius: 8px; background: #fafafa;">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                            <strong>Заказ №<?= $order['id'] ?></strong>
                            <span style="font-weight: 600;">
                                <?= number_format($order['total'], 0, '', ' ') ?> ₽
                            </span>
                        </div>
                        <div style="margin: 10px 0; color: #666; font-size: 14px;">
                            <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                        </div>
                        <div>
                            <span style="
                                padding: 4px 12px;
                                border-radius: 20px;
                                font-size: 13px;
                                font-weight: 600;
                                <?php
                                $status = $order['status'];
                                if ($status === 'Обрабатывается') echo 'background: #fff3cd; color: #856404;';
                                elseif ($status === 'В пути') echo 'background: #d1ecf1; color: #0c5460;';
                                else echo 'background: #d4edda; color: #155724;';
                                ?>
                            ">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>
                        <!-- Позже можно добавить "Посмотреть детали" -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'footer.php'; ?>