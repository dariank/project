<?php
session_start();
require_once 'db.php';

$error = '';
if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Заполните все поля.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: profile.php');
            exit;
        } else {
            $error = 'Неверный email или пароль.';
        }
    }
}
?>

<?php require_once 'header.php'; ?>

<main class="container" style="max-width: 500px; margin: 60px auto; position: relative;">
    <h2 style="text-align: center; margin-bottom: 30px;">Вход в аккаунт</h2>

    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
        </div>
        <div style="position: relative;">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required minlength="6"
                   style="width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
            <button type="button" 
                    onclick="togglePasswordVisibility('password')" 
                    style="position: absolute; right: 12px; top: 42px; background: none; border: none; cursor: pointer; font-size: 18px; color: #888;"
                    aria-label="Показать пароль">
                👁️
            </button>
        </div>
        <button type="submit" class="btn" style="padding: 12px; font-size: 16px;">Войти</button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <p>Нет аккаунта? <a href="register.php" style="color: #000; text-decoration: underline;">Создать</a></p>
    </div>
</main>

<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>

<?php require_once 'footer.php'; ?>