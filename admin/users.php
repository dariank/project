<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$_GET['id']]);
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<style>
    .admin-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 10px;
        font-family: Arial, sans-serif;
    }

    h2 {
        margin-bottom: 20px;
        font-size: 28px;
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

    .delete-btn {
        color: #d00;
        text-decoration: none;
        font-weight: bold;
    }

    .delete-btn:hover {
        text-decoration: underline;
    }
</style>

<div class="admin-container">

    <h2>Пользователи</h2>

   <a href="index.php" style="color: #000; text-decoration: underline; font-size: 16px;">
    ← В админ-панель
</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>

        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['role'] ?></td>
                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <a class="delete-btn" href="?action=delete&id=<?= $u['id'] ?>" onclick="return confirm('Удалить пользователя?')">Удалить</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>
