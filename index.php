<?php
// Страница авторизации. Проверяет логин/пароль в БД и роль пользователя.
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Введите логин и пароль.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT u.id, u.fio, u.login, u.password, r.name AS role
             FROM users u JOIN roles r ON u.role_id = r.id
             WHERE u.login = ? AND u.password = ?'
        );
        $stmt->execute([$login, $password]);
        $u = $stmt->fetch();

        if ($u) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['fio']     = $u['fio'];
            $_SESSION['role']    = $u['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль.';
        }
    }
}

if (isset($_GET['guest'])) {
    $_SESSION['user_id'] = 0;
    $_SESSION['fio']     = 'Гость';
    $_SESSION['role']    = 'Guest';
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Магазин игрушек - Авторизация</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="icon.png">
</head>
<body>
    <div class="login-box">
        <h1><img src="icon.png" style="width:32px; height:32px; vertical-align:middle; margin-right:8px;" alt="">ООО "МирИгрушек"</h1>
        <p class="muted" style="text-align:center;">Информационная система. Вход.</p>

        <form method="post" action="index.php">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width:100%;">Войти</button>
        </form>

        <hr style="margin:16px 0; border-color:#DEB887;">
        <a class="btn-link" href="index.php?guest=1">Продолжить как гость (просмотр товаров)</a>
    </div>

    <script>
    function showToast(type, title, text) {
        var container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "toast-container";
            document.body.appendChild(container);
        }
        
        var card = document.createElement("div");
        card.className = "toast-card toast-card-" + type;
        
        var icon = "ℹ️";
        if (type === "success") icon = "✅";
        if (type === "error") icon = "❌";
        if (type === "warning") icon = "⚠️";
        
        card.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-text">${text}</div>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        container.appendChild(card);
        
        setTimeout(function() {
            card.classList.add("show");
        }, 10);
        
        var autoHideTimer = setTimeout(function() {
            hideToast(card);
        }, 5000);
        
        card.querySelector(".toast-close").addEventListener("click", function() {
            clearTimeout(autoHideTimer);
            hideToast(card);
        });
    }

    function hideToast(card) {
        card.classList.add("hide");
        card.classList.remove("show");
        setTimeout(function() {
            if (card.parentNode) {
                card.parentNode.removeChild(card);
            }
        }, 300);
    }

    <?php if ($error): ?>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("error", "Ошибка входа", "<?= htmlspecialchars($error) ?>");
    });
    <?php endif; ?>
    </script>
</body>
</html>
