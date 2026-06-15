<?php
// Единая панель управления ИС "Магазин игрушек".
// Разделение интерфейса по ролям: Guest, Authorized client, Manager, Administrator.
session_start();
require 'db.php';

if (!isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$role  = $_SESSION['role'];
$fio   = $_SESSION['fio'];
$isAdmin    = ($role === 'Administrator');
$isManager  = ($role === 'Manager');
$canFilter  = ($isAdmin || $isManager);
$canOrders  = ($isAdmin || $isManager);

$message = '';

// Перевод ролей для отображения
$roleNames = [
    'Administrator' => 'Администратор',
    'Manager' => 'Менеджер',
    'Authorized client' => 'Авторизованный клиент',
    'Guest' => 'Гость'
];
$roleDisplay = $roleNames[$role] ?? $role;

// ============================================================
// ОБРАБОТКА ДЕЙСТВИЙ (POST и GET) - только для администратора
// ============================================================
if ($isAdmin) {
    // --- Сохранение товара (добавление/редактирование) ---
    if (isset($_POST['save_product'])) {
        $id             = $_POST['id'] ?? '';
        $article        = trim($_POST['article'] ?? '');
        $name           = trim($_POST['name'] ?? '');
        $category_id    = (int)($_POST['category_id'] ?? 0);
        $supplier_id    = (int)($_POST['supplier_id'] ?? 0);
        $manufacturer_id= (int)($_POST['manufacturer_id'] ?? 0);
        $description    = trim($_POST['description'] ?? '');
        $price          = (float)str_replace(',', '.', $_POST['price'] ?? '0');
        $unit           = trim($_POST['unit'] ?? 'шт.');
        $stock          = (int)($_POST['stock'] ?? 0);
        $discount       = (int)($_POST['discount'] ?? 0);
        $old_photo      = $_POST['old_photo'] ?? '';

        if ($price < 0 || $stock < 0 || $discount < 0 || $discount > 100) {
            $message = "<div class='msg msg-error'>Цена и остаток не могут быть отрицательными; скидка от 0 до 100.</div>";
        } elseif ($article === '' || $name === '') {
            $message = "<div class='msg msg-error'>Артикул и наименование обязательны.</div>";
        } else {
            $photo_path = $old_photo;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['photo']['tmp_name'];
                $info = getimagesize($tmp);
                if ($info === false) {
                    $message = "<div class='msg msg-error'>Загруженный файл не является изображением.</div>";
                } else {
                    $w = $info[0]; $h = $info[1];
                    if ($w > 300 || $h > 200) {
                        $message = "<div class='msg msg-error'>Размер фото должен быть не более 300x200 px (получено ${w}x${h}).</div>";
                    } else {
                        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $message = "<div class='msg msg-error'>Допустимые форматы: jpg, png, gif.</div>";
                        } else {
                            $new_name = 'uploads/' . uniqid('p_', true) . '.' . $ext;
                            if (!is_dir('uploads')) { mkdir('uploads', 0775, true); }
                            if (move_uploaded_file($tmp, $new_name)) {
                                if ($old_photo && $old_photo !== 'picture.png' && is_file($old_photo)) {
                                    @unlink($old_photo);
                                }
                                $photo_path = $new_name;
                            }
                        }
                    }
                }
            }

            if (strpos($message, 'msg-error') === false) {
                if ($id === '') {
                    $stmt = $pdo->prepare(
                        'INSERT INTO products
                         (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
                    );
                    $stmt->execute([$article, $name, $category_id, $supplier_id, $manufacturer_id, $description, $price, $unit, $stock, $discount, $photo_path]);
                    $message = "<div class='msg msg-ok'>Товар добавлен.</div>";
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE products SET
                            article=?, name=?, category_id=?, supplier_id=?, manufacturer_id=?,
                            description=?, price=?, unit=?, stock=?, discount=?, photo=?
                         WHERE id=?'
                    );
                    $stmt->execute([$article, $name, $category_id, $supplier_id, $manufacturer_id, $description, $price, $unit, $stock, $discount, $photo_path, $id]);
                    $message = "<div class='msg msg-ok'>Товар обновлён.</div>";
                }
            }
        }
    }

    // --- Удаление товара ---
    if (isset($_GET['action']) && $_GET['action'] === 'delete_product') {
        $pid = (int)$_GET['id'];
        $check = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $check->execute([$pid]);
        if ($check->fetchColumn() > 0) {
            $message = "<div class='msg msg-error'>Невозможно удалить: товар входит в состав заказа.</div>";
        } else {
            $p = $pdo->prepare('SELECT photo FROM products WHERE id=?');
            $p->execute([$pid]);
            $photo = $p->fetchColumn();
            if ($photo && $photo !== 'picture.png' && is_file($photo)) { @unlink($photo); }
            $del = $pdo->prepare('DELETE FROM products WHERE id=?');
            $del->execute([$pid]);
            $message = "<div class='msg msg-ok'>Товар удалён.</div>";
        }
    }

    // --- Сохранение заказа (добавление/редактирование) ---
    if (isset($_POST['save_order'])) {
        $oid             = $_POST['id'] ?? '';
        $article         = trim($_POST['article'] ?? '');
        $status_id       = (int)($_POST['status_id'] ?? 0);
        $pickup_point_id = (int)($_POST['pickup_point_id'] ?? 0);
        $order_date      = trim($_POST['order_date'] ?? '');
        $delivery_date   = trim($_POST['delivery_date'] ?? '');
        $pickup_code     = trim($_POST['pickup_code'] ?? '');

        if ($article === '') {
            $message = "<div class='msg msg-error'>Артикул заказа обязателен.</div>";
        } else {
            $order_date    = ($order_date    === '') ? null : $order_date;
            $delivery_date = ($delivery_date === '') ? null : $delivery_date;
            if ($oid === '') {
                $stmt = $pdo->prepare(
                    'INSERT INTO orders (article, order_date, delivery_date, pickup_point_id, status_id, pickup_code)
                     VALUES (?,?,?,?,?,?)'
                );
                $stmt->execute([$article, $order_date, $delivery_date, $pickup_point_id, $status_id, $pickup_code]);
                $message = "<div class='msg msg-ok'>Заказ добавлен.</div>";
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE orders SET article=?, order_date=?, delivery_date=?, pickup_point_id=?, status_id=?, pickup_code=?
                     WHERE id=?'
                );
                $stmt->execute([$article, $order_date, $delivery_date, $pickup_point_id, $status_id, $pickup_code, $oid]);
                $message = "<div class='msg msg-ok'>Заказ обновлён.</div>";
            }
        }
    }

    // --- Удаление заказа ---
    if (isset($_GET['action']) && $_GET['action'] === 'delete_order') {
        $oid = (int)$_GET['id'];
        $pdo->prepare('DELETE FROM orders WHERE id=?')->execute([$oid]);
        $message = "<div class='msg msg-ok'>Заказ удалён.</div>";
    }
}

$action = $_GET['action'] ?? '';
$view   = $_GET['view'] ?? 'products';

$categories     = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$suppliers      = $pdo->query('SELECT * FROM suppliers ORDER BY name')->fetchAll();
$manufacturers  = $pdo->query('SELECT * FROM manufacturers ORDER BY name')->fetchAll();
$statuses       = $pdo->query('SELECT * FROM order_statuses ORDER BY id')->fetchAll();
$pickup_points  = $pdo->query('SELECT * FROM pickup_points ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Панель управления - Магазин игрушек</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="icon.png">
</head>
<body>
    <div class="header">
        <div class="logo">ООО "МирИгрушек"</div>
        <div class="user-info">
            <?= htmlspecialchars($fio) ?> (<?= htmlspecialchars($roleDisplay) ?>)
            &nbsp; <a class="btn-link" href="logout.php">Выйти</a>
        </div>
    </div>

    <div class="container">

        <?php
        // ============================================================
        // ФОРМЫ ТОВАРОВ (только администратор)
        // ============================================================
        if ($isAdmin && ($action === 'add_product' || $action === 'edit_product')) {
            $p = ['id'=>'','article'=>'','name'=>'','category_id'=>'','supplier_id'=>'','manufacturer_id'=>'','description'=>'','price'=>'','unit'=>'шт.','stock'=>'','discount'=>'','photo'=>''];
            if ($action === 'edit_product') {
                $pid = (int)$_GET['id'];
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
                $stmt->execute([$pid]);
                $p = $stmt->fetch();
            }
            ?>
            <h2><?= $action === 'add_product' ? 'Добавить товар' : 'Редактировать товар' ?></h2>
            <form method="post" action="dashboard.php" enctype="multipart/form-data">
                <input type="hidden" name="save_product" value="1">
                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                <input type="hidden" name="old_photo" value="<?= htmlspecialchars($p['photo'] ?? '') ?>">

                <?php if ($action === 'edit_product'): ?>
                <div class="form-group"><label>ID (только чтение)</label>
                    <input type="text" value="<?= htmlspecialchars($p['id']) ?>" readonly></div>
                <?php endif; ?>

                <div class="form-group"><label>Артикул *</label>
                    <input type="text" name="article" value="<?= htmlspecialchars($p['article']) ?>" required></div>

                <div class="form-group"><label>Наименование *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required></div>

                <div class="form-group"><label>Категория</label>
                    <select name="category_id">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $p['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Поставщик</label>
                    <select name="supplier_id">
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $p['supplier_id']==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Производитель</label>
                    <select name="manufacturer_id">
                        <?php foreach ($manufacturers as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $p['manufacturer_id']==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Описание</label>
                    <textarea name="description" rows="3"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>

                <div class="form-group"><label>Цена (>= 0)</label>
                    <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($p['price']) ?>" required></div>

                <div class="form-group"><label>Единица измерения</label>
                    <input type="text" name="unit" value="<?= htmlspecialchars($p['unit']) ?>"></div>

                <div class="form-group"><label>Остаток на складе (>= 0)</label>
                    <input type="number" min="0" name="stock" value="<?= htmlspecialchars($p['stock']) ?>" required></div>

                <div class="form-group"><label>Скидка, % (0..100)</label>
                    <input type="number" min="0" max="100" name="discount" value="<?= htmlspecialchars($p['discount']) ?>"></div>

                <div class="form-group"><label>Фото (макс. 300x200 px; jpg/png/gif)</label>
                    <?php if (!empty($p['photo'])): ?>
                        <div><img src="<?= htmlspecialchars($p['photo']) ?>" class="row-img"> <span class="muted">Текущее фото</span></div>
                    <?php endif; ?>
                    <input type="file" name="photo" accept="image/*"></div>

                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить</button>
                    <a class="btn" href="dashboard.php?view=products">Назад</a>
                </div>
            </form>
        <?php
            echo "</div></body></html>";
            exit;
        }

        // ============================================================
        // ФОРМЫ ЗАКАЗОВ (только администратор)
        // ============================================================
        if ($isAdmin && ($action === 'add_order' || $action === 'edit_order')) {
            $o = ['id'=>'','article'=>'','order_date'=>'','delivery_date'=>'','pickup_point_id'=>'','status_id'=>'','pickup_code'=>''];
            if ($action === 'edit_order') {
                $oid = (int)$_GET['id'];
                $stmt = $pdo->prepare('SELECT * FROM orders WHERE id=?');
                $stmt->execute([$oid]);
                $o = $stmt->fetch();
            }
            ?>
            <h2><?= $action === 'add_order' ? 'Добавить заказ' : 'Редактировать заказ' ?></h2>
            <form method="post" action="dashboard.php">
                <input type="hidden" name="save_order" value="1">
                <input type="hidden" name="id" value="<?= htmlspecialchars($o['id']) ?>">

                <?php if ($action === 'edit_order'): ?>
                <div class="form-group"><label>ID (только чтение)</label>
                    <input type="text" value="<?= htmlspecialchars($o['id']) ?>" readonly></div>
                <?php endif; ?>

                <div class="form-group"><label>Артикул *</label>
                    <input type="text" name="article" value="<?= htmlspecialchars($o['article']) ?>" required></div>

                <div class="form-group"><label>Статус заказа</label>
                    <select name="status_id">
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= $o['status_id']==$st['id']?'selected':'' ?>><?= htmlspecialchars($st['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Адрес пункта выдачи</label>
                    <select name="pickup_point_id">
                        <?php foreach ($pickup_points as $pp): ?>
                            <option value="<?= $pp['id'] ?>" <?= $o['pickup_point_id']==$pp['id']?'selected':'' ?>><?= htmlspecialchars($pp['address']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Дата заказа</label>
                    <input type="date" name="order_date" value="<?= htmlspecialchars($o['order_date']) ?>"></div>

                <div class="form-group"><label>Дата выдачи</label>
                    <input type="date" name="delivery_date" value="<?= htmlspecialchars($o['delivery_date']) ?>"></div>

                <div class="form-group"><label>Код получения</label>
                    <input type="text" name="pickup_code" value="<?= htmlspecialchars($o['pickup_code']) ?>"></div>

                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить</button>
                    <a class="btn" href="dashboard.php?view=orders">Назад</a>
                </div>
            </form>
        <?php
            echo "</div></body></html>";
            exit;
        }

        // Навигация по разделам
        echo '<div class="nav">';
        echo '<a class="btn" href="dashboard.php?view=products">Товары</a>';
        if ($canOrders) {
            echo '<a class="btn" href="dashboard.php?view=orders">Заказы</a>';
        }
        echo '</div>';
        ?>

        <?php
        // ============================================================
        // РАЗДЕЛ ТОВАРОВ
        // ============================================================
        if ($view === 'products'):
            $search   = $_GET['search']   ?? '';
            $supplier = $_GET['supplier'] ?? '';
            $sort     = $_GET['sort']     ?? '';
            $dir      = $_GET['dir']      ?? 'asc';

            $where = [];
            $params = [];
            if ($canFilter && $search !== '') {
                $where[] = '(p.name LIKE ? OR p.article LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR m.name LIKE ? OR s.name LIKE ?)';
                $kw = '%' . $search . '%';
                array_push($params, $kw, $kw, $kw, $kw, $kw, $kw);
            }
            if ($canFilter && $supplier !== '') {
                $where[] = 'p.supplier_id = ?';
                $params[] = (int)$supplier;
            }
            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $orderSql = 'ORDER BY p.id';
            if ($canFilter && $sort !== '') {
                $allowed = ['price' => 'p.price', 'stock' => 'p.stock'];
                if (isset($allowed[$sort])) {
                    $dir = ($dir === 'desc') ? 'DESC' : 'ASC';
                    $orderSql = "ORDER BY {$allowed[$sort]} $dir";
                }
            }

            $sql = "SELECT p.*, c.name AS category, s.name AS supplier, m.name AS manufacturer
                    FROM products p
                    JOIN categories c     ON p.category_id = c.id
                    JOIN suppliers s      ON p.supplier_id = s.id
                    JOIN manufacturers m  ON p.manufacturer_id = m.id
                    $whereSql $orderSql";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            ?>

            <h2>Товары</h2>

            <?php if ($canFilter): ?>
            <form class="filters" method="get">
                <input type="hidden" name="view" value="products">
                <div class="form-group">
                    <label>Поиск</label>
                    <input type="text" id="search-input" name="search" value="<?= htmlspecialchars($search) ?>"
                           placeholder="наименование, артикул, описание..." oninput="this.form.submit()">
                </div>
                <div class="form-group">
                    <label>Поставщик</label>
                    <select name="supplier" onchange="this.form.submit()">
                        <option value="">Все поставщики</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $supplier==(string)$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Сортировка</label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Без сортировки</option>
                        <option value="price" <?= $sort==='price'?'selected':'' ?>>По цене</option>
                        <option value="stock" <?= $sort==='stock'?'selected':'' ?>>По остатку</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Направление</label>
                    <select name="dir" onchange="this.form.submit()">
                        <option value="asc"  <?= $dir==='asc'?'selected':'' ?>>По возрастанию</option>
                        <option value="desc" <?= $dir==='desc'?'selected':'' ?>>По убыванию</option>
                    </select>
                </div>
            </form>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <div style="margin-bottom:12px;">
                    <a class="btn" href="dashboard.php?action=add_product">Добавить товар</a>
                </div>
            <?php endif; ?>

            <div class="product-cards">
                <?php foreach ($products as $p):
                    $cardClass = 'product-card';
                    if ((int)$p['stock'] === 0) {
                        $cardClass .= ' row-out-of-stock';
                    } elseif ((int)$p['discount'] > 17) {
                        $cardClass .= ' row-discount';
                    }
                    $photo = !empty($p['photo']) && is_file($p['photo']) ? $p['photo'] : 'picture.png';
                    $price = (float)$p['price'];
                    $disc  = (int)$p['discount'];
                    $finalPrice = $disc > 0 ? round($price * (100 - $disc) / 100, 2) : $price;
                ?>
                    <div class="<?= $cardClass ?>" <?php if ($isAdmin): ?>onclick="window.location='dashboard.php?action=edit_product&id=<?= $p['id'] ?>'" style="cursor:pointer;"<?php endif; ?>>
                        <div class="card-left">
                            <img src="<?= htmlspecialchars($photo) ?>" class="card-img" alt="">
                        </div>
                        <div class="card-middle">
                            <div class="card-title"><?= htmlspecialchars($p['category']) ?> | <?= htmlspecialchars($p['name']) ?></div>
                            <div class="card-details">
                                <p><b>Описание товара:</b> <?= htmlspecialchars($p['description']) ?></p>
                                <p><b>Производитель:</b> <?= htmlspecialchars($p['manufacturer']) ?></p>
                                <p><b>Поставщик:</b> <?= htmlspecialchars($p['supplier']) ?></p>
                                <p><b>Цена:</b> 
                                    <?php if ($disc > 0): ?>
                                        <span class="price-old"><?= number_format($price, 2) ?></span>
                                        <span class="price-new"><?= number_format($finalPrice, 2) ?></span>
                                    <?php else: ?>
                                        <?= number_format($price, 2) ?>
                                    <?php endif; ?>
                                </p>
                                <p><b>Ед. измерения:</b> <?= htmlspecialchars($p['unit']) ?></p>
                                <p><b>Количество на складе:</b> <?= (int)$p['stock'] ?></p>
                            </div>
                        </div>
                        <div class="card-right">
                            <?php if ($disc > 0): ?>
                                <div class="discount-badge">Действующая скидка<br><span class="discount-val"><?= $disc ?>%</span></div>
                            <?php else: ?>
                                <div class="discount-badge-none">Нет скидки</div>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                                <div class="card-actions">
                                    <a class="btn-link btn-danger-link delete-link" href="dashboard.php?action=delete_product&id=<?= $p['id'] ?>"
                                       onclick="event.stopPropagation(); event.preventDefault(); showConfirmModal('Вы действительно хотите удалить этот товар?', () => { window.location.href = 'dashboard.php?action=delete_product&id=<?= $p['id'] ?>'; });">Удалить</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <?php
        // ============================================================
        // РАЗДЕЛ ЗАКАЗОВ (менеджер/администратор)
        // ============================================================
        if ($view === 'orders' && $canOrders):
            $sql = "SELECT o.*, s.name AS status, pp.address,
                           u.fio AS client_fio
                    FROM orders o
                    JOIN order_statuses s ON o.status_id = s.id
                    JOIN pickup_points pp ON o.pickup_point_id = pp.id
                    LEFT JOIN users u     ON o.user_id = u.id
                    ORDER BY o.id";
            $orders = $pdo->query($sql)->fetchAll();
            ?>
            <h2>Заказы</h2>
            <?php if ($isAdmin): ?>
                <div style="margin-bottom:12px;">
                    <a class="btn" href="dashboard.php?action=add_order">Добавить заказ</a>
                </div>
            <?php endif; ?>

            <div class="order-cards">
                <?php foreach ($orders as $o):
                    $items = $pdo->prepare(
                        'SELECT p.article, oi.quantity
                         FROM order_items oi JOIN products p ON oi.product_id = p.id
                         WHERE oi.order_id = ?'
                    );
                    $items->execute([$o['id']]);
                    $rows = $items->fetchAll();
                    $itemsStr = implode(', ', array_map(fn($r) => "{$r['article']} x{$r['quantity']}", $rows));
                    
                    $statusDisplay = ($o['status'] === 'Completed') ? 'Завершен' : 'Новый';
                ?>
                    <div class="order-card" <?php if ($isAdmin): ?>onclick="window.location='dashboard.php?action=edit_order&id=<?= $o['id'] ?>'" style="cursor:pointer;"<?php endif; ?>>
                        <div class="card-left">
                            <div class="card-title"><?= htmlspecialchars($o['article']) ?></div>
                            <div class="card-details">
                                <p><b>Статус:</b> <span class="status-badge status-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($statusDisplay) ?></span></p>
                                <p><b>Адрес пункта выдачи:</b> <?= htmlspecialchars($o['address']) ?></p>
                                <p><b>Дата заказа:</b> <?= htmlspecialchars($o['order_date'] ?? '-') ?></p>
                            </div>
                        </div>
                        <div class="card-middle">
                            <div class="card-details">
                                <p><b>Клиент:</b> <?= htmlspecialchars($o['client_fio'] ?? '-') ?></p>
                                <p><b>Код получения:</b> <span class="pickup-code-badge"><?= htmlspecialchars($o['pickup_code'] ?? '-') ?></span></p>
                                <p><b>Состав заказа:</b> <span class="order-items-list"><?= htmlspecialchars($itemsStr) ?></span></p>
                            </div>
                        </div>
                        <div class="card-right">
                            <div class="delivery-date-container">
                                <span class="delivery-label">Дата доставки</span>
                                <span class="delivery-date-val"><?= htmlspecialchars($o['delivery_date'] ?? '-') ?></span>
                            </div>
                            <?php if ($isAdmin): ?>
                                <div class="card-actions">
                                    <a class="btn-link btn-danger-link delete-link" href="dashboard.php?action=delete_order&id=<?= $o['id'] ?>"
                                       onclick="event.stopPropagation(); event.preventDefault(); showConfirmModal('Вы действительно хотите удалить этот заказ?', () => { window.location.href = 'dashboard.php?action=delete_order&id=<?= $o['id'] ?>'; });">Удалить</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
    <!-- Confirm Modal Overlay -->
    <div id="confirm-modal-overlay" class="modal-overlay" style="display:none;">
        <div class="confirm-modal">
            <p id="confirm-modal-text" style="font-size: 15px; margin-bottom: 20px; line-height: 1.5; color: #333; font-weight: bold;"></p>
            <div class="modal-footer">
                <button id="confirm-modal-cancel" class="btn">Отмена</button>
                <button id="confirm-modal-ok" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>

    <script>
    // Toast notification functions
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

    // Modal confirm logic
    var pendingAction = null;
    function showConfirmModal(text, onConfirm) {
        var overlay = document.getElementById("confirm-modal-overlay");
        var textEl = document.getElementById("confirm-modal-text");
        textEl.textContent = text;
        overlay.style.display = "flex";
        pendingAction = onConfirm;
    }

    document.getElementById("confirm-modal-cancel").addEventListener("click", function() {
        document.getElementById("confirm-modal-overlay").style.display = "none";
        pendingAction = null;
    });

    document.getElementById("confirm-modal-ok").addEventListener("click", function() {
        document.getElementById("confirm-modal-overlay").style.display = "none";
        if (pendingAction) {
            pendingAction();
        }
    });



    document.addEventListener("DOMContentLoaded", function() {
        // Restore search focus if needed
        var searchInput = document.getElementById("search-input");
        if (searchInput && (window.location.search.indexOf("search=") !== -1)) {
            searchInput.focus();
            var val = searchInput.value;
            searchInput.setSelectionRange(val.length, val.length);
        }

        // Trigger toast if PHP set a message
        <?php
        $toastType = '';
        $toastText = '';
        $toastTitle = '';
        if (!empty($message)) {
            if (strpos($message, 'msg-error') !== false) {
                $toastType = 'error';
                $toastTitle = 'Ошибка';
            } elseif (strpos($message, 'msg-ok') !== false) {
                $toastType = 'success';
                $toastTitle = 'Успешно';
            } elseif (strpos($message, 'msg-warn') !== false) {
                $toastType = 'warning';
                $toastTitle = 'Предупреждение';
            } else {
                $toastType = 'info';
                $toastTitle = 'Информация';
            }
            $toastText = strip_tags($message);
        }
        if (!empty($toastType)): ?>
            showToast("<?= $toastType ?>", "<?= $toastTitle ?>", "<?= htmlspecialchars($toastText) ?>");
        <?php endif; ?>
    });
    </script>
</body>
</html>
