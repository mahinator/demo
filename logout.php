<?php
// Выход из системы: уничтожение сессии и возврат на страницу авторизации
session_start();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
