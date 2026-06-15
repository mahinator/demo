DROP DATABASE IF EXISTS demo;
CREATE DATABASE demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE demo;

-- Roles
CREATE TABLE roles (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO roles (id, name) VALUES (1, 'Administrator'), (2, 'Manager'), (3, 'Authorized client');

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fio VARCHAR(150) NOT NULL,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
INSERT INTO users (fio, login, password, role_id) VALUES ('Ворсин Петр Евгеньевич', '94d5ous@gmail.com', 'uzWC67', 1);
INSERT INTO users (fio, login, password, role_id) VALUES ('Старикова Елена Павловна', 'uth4iz@mail.com', '2L6KZG', 1);
INSERT INTO users (fio, login, password, role_id) VALUES ('Одинцов Серафим Артёмович', 'yzls62@outlook.com', 'JlFRCZ', 1);
INSERT INTO users (fio, login, password, role_id) VALUES ('Михайлюк Анна Вячеславовна', '1diph5e@tutanota.com', '8ntwUp', 2);
INSERT INTO users (fio, login, password, role_id) VALUES ('Ситдикова Елена Анатольевна', 'tjde7c@yahoo.com', 'YOyhfR', 2);
INSERT INTO users (fio, login, password, role_id) VALUES ('Никифорова Весения Николаевна', 'wpmrc3do@tutanota.com', 'RSbvHv', 2);
INSERT INTO users (fio, login, password, role_id) VALUES ('Степанов Михаил Артёмович', '5d4zbu@tutanota.com', 'rwVDh9', 3);
INSERT INTO users (fio, login, password, role_id) VALUES ('Ворсин Петр Евгеньевич', 'ptec8ym@yahoo.com', 'LdNyos', 3);
INSERT INTO users (fio, login, password, role_id) VALUES ('Старикова Елена Павловна', '1qz4kw@mail.com', 'gynQMT', 3);
INSERT INTO users (fio, login, password, role_id) VALUES ('Сазонов Руслан Германович', '4np6se@mail.com', 'AtnDjr', 3);

-- Categories
CREATE TABLE categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO categories (id, name) VALUES (1, 'Детский музыкальный инструмент');
INSERT INTO categories (id, name) VALUES (2, 'Игровой набор');
INSERT INTO categories (id, name) VALUES (3, 'Конструктор');
INSERT INTO categories (id, name) VALUES (4, 'Машинка');

-- Suppliers
CREATE TABLE suppliers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO suppliers (id, name) VALUES (1, 'CHILITOY');
INSERT INTO suppliers (id, name) VALUES (2, 'Knauf');
INSERT INTO suppliers (id, name) VALUES (3, 'Pikeshop');
INSERT INTO suppliers (id, name) VALUES (4, 'Playbig');
INSERT INTO suppliers (id, name) VALUES (5, 'Vinylon');

-- Manufacturers
CREATE TABLE manufacturers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO manufacturers (id, name) VALUES (1, 'ABSпластик');
INSERT INTO manufacturers (id, name) VALUES (2, 'BambiniFelici');
INSERT INTO manufacturers (id, name) VALUES (3, 'Junion');

-- Products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    manufacturer_id INT NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'шт.',
    stock INT NOT NULL DEFAULT 0,
    discount INT NOT NULL DEFAULT 0,
    photo VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('PMEZMH', 'Детский игровой набор машинок Щенячий патруль / Dogs mini . 9 героев + 9 инерфионных машинок', 2, 3, 1, 'Детский набор машинок с героями мультсериала «Щенячий патруль» подойдет как для мальчиков, так и для девочек. В детский набор входит 9 фигурок щенков спасателей.', 1414.0, 'шт.', 50, 22, '1.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('BPV4MM', 'Конструктор Гарри Поттер Сова Букля 630 деталей совместим с lego harry potter, лего совместимый)', 3, 4, 1, 'Коллекционная модель Букля состоит из множества потрясающих элементов, а также специального механизма внутри. С его помощью можно плавно поднимать-опускать крылья птицы.', 771.0, 'шт.', 26, 15, '2.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('JVL42J', 'Музыкальные инструменты для детей, ксилофон, барабаны, развивающие игрушки, игрушки для детей', 1, 4, 2, 'Откройте мир музыки для вашего ребенка с этой уникальной игрушкой! Это многофункциональное музыкальное чудо объединяет в себе всё, что нужно для творческого развития.', 2750.0, 'шт.', 0, 15, '3.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('F895RB', 'Машинка игрушка диско шар светящаяся музыкальная', 4, 2, 1, 'Светящаяся музыкальная машина с диско шаром переливается разными цветами, играет ритмичные мелодии, объезжает препятствия и крутится, поэтому с ней точно не будет скучно.', 368.0, 'шт.', 7, 6, '4.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('3XBOTN', 'Игровой набор Hot Wheels Action Loop Cyclone Challenge Track, с машинкой и удобным хранением, HTK16', 2, 2, 2, 'Игровой набор Hot Wheels Action Loop Cyclone Challenge Track - это уникальная игра, которая позволит вам испытать себя и своих друзей в скорости и ловкости. Этот набор состоит из металлической дорожки с циклоном, которая создает потрясающий эффект и добавляет дополнительную сложность в игру.', 3426.0, 'шт.', 21, 10, '5.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('3L7RCZ', 'Игровой набор с деревянными машинками Стройплощадка Кран-Паркс, Junion', 2, 2, 3, 'Игровой набор «Стройплощадка Кран-Паркс Junion» — это большая игрушечная парковка с деревянными машинками и настоящим подъёмным краном, придуманная в Яндексе настоящими родителями.', 7400.0, 'шт.', 0, 15, '6.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('S72AM3', 'Синтезатор детский с микрофоном 61 клавиша', 1, 1, 3, 'Откройте для ребенка дверь в мир музыки с детским синтезатором! Этот компактный инструмент с микрофоном станет верным другом для юных музыкантов, помогая им развивать творческий потенциал и получать удовольствие от игры.', 1749.0, 'шт.', 35, 10, '7.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('2G3280', 'Деревянный игровой набор JUNION Стройплощадка "Кран-Паркс" с подъёмным, строительным краном и машинками, 18 предметов, подвижные элементы', 2, 5, 3, 'Игровой набор «Стройплощадка Кран-Паркс Junion» — это большая игрушечная парковка с деревянными машинками и настоящим подъёмным краном, придуманная в Яндексе настоящими родителями.', 1624.0, 'шт.', 20, 9, '8.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('MIO8YV', 'Музыкальная игрушка интерактивная Пульт, детский прорезыватель для малышей', 1, 5, 2, 'Музыкальная игрушка интерактивная Пульт, детский прорезыватель для малышей', 305.0, 'шт.', 31, 9, '9.jpg');
INSERT INTO products (article, name, category_id, supplier_id, manufacturer_id, description, price, unit, stock, discount, photo)
VALUES ('UER2QD', 'Большой набор опытов и экспериментов для детей 14 в 1', 2, 5, 2, 'Большой набор опытов и экспериментов для детей 14 в 1', 2506.0, 'шт.', 27, 8, '10.jpg');

-- Pickup points
CREATE TABLE pickup_points (id INT AUTO_INCREMENT PRIMARY KEY, address VARCHAR(255) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO pickup_points (id, address) VALUES (1, '420151, г. Лесной, ул. Вишневая, 32');
INSERT INTO pickup_points (id, address) VALUES (2, '125061, г. Лесной, ул. Подгорная, 8');
INSERT INTO pickup_points (id, address) VALUES (3, '630370, г. Лесной, ул. Шоссейная, 24');
INSERT INTO pickup_points (id, address) VALUES (4, '400562, г. Лесной, ул. Зеленая, 32');
INSERT INTO pickup_points (id, address) VALUES (5, '614510, г. Лесной, ул. Маяковского, 47');
INSERT INTO pickup_points (id, address) VALUES (6, '410542, г. Лесной, ул. Светлая, 46');
INSERT INTO pickup_points (id, address) VALUES (7, '620839, г. Лесной, ул. Цветочная, 8');
INSERT INTO pickup_points (id, address) VALUES (8, '443890, г. Лесной, ул. Коммунистическая, 1');
INSERT INTO pickup_points (id, address) VALUES (9, '603379, г. Лесной, ул. Спортивная, 46');
INSERT INTO pickup_points (id, address) VALUES (10, '603721, г. Лесной, ул. Гоголя, 41');
INSERT INTO pickup_points (id, address) VALUES (11, '410172, г. Лесной, ул. Северная, 13');
INSERT INTO pickup_points (id, address) VALUES (12, '614611, г. Лесной, ул. Молодежная, 50');
INSERT INTO pickup_points (id, address) VALUES (13, '454311, г.Лесной, ул. Новая, 19');
INSERT INTO pickup_points (id, address) VALUES (14, '660007, г.Лесной, ул. Октябрьская, 19');
INSERT INTO pickup_points (id, address) VALUES (15, '603036, г. Лесной, ул. Садовая, 4');
INSERT INTO pickup_points (id, address) VALUES (16, '394060, г.Лесной, ул. Фрунзе, 43');
INSERT INTO pickup_points (id, address) VALUES (17, '410661, г. Лесной, ул. Школьная, 50');
INSERT INTO pickup_points (id, address) VALUES (18, '625590, г. Лесной, ул. Коммунистическая, 20');
INSERT INTO pickup_points (id, address) VALUES (19, '625683, г. Лесной, ул. 8 Марта');
INSERT INTO pickup_points (id, address) VALUES (20, '450983, г.Лесной, ул. Комсомольская, 26');
INSERT INTO pickup_points (id, address) VALUES (21, '394782, г. Лесной, ул. Чехова, 3');
INSERT INTO pickup_points (id, address) VALUES (22, '603002, г. Лесной, ул. Дзержинского, 28');
INSERT INTO pickup_points (id, address) VALUES (23, '450558, г. Лесной, ул. Набережная, 30');
INSERT INTO pickup_points (id, address) VALUES (24, '344288, г. Лесной, ул. Чехова, 1');
INSERT INTO pickup_points (id, address) VALUES (25, '614164, г.Лесной,  ул. Степная, 30');
INSERT INTO pickup_points (id, address) VALUES (26, '394242, г. Лесной, ул. Коммунистическая, 43');
INSERT INTO pickup_points (id, address) VALUES (27, '660540, г. Лесной, ул. Солнечная, 25');
INSERT INTO pickup_points (id, address) VALUES (28, '125837, г. Лесной, ул. Шоссейная, 40');
INSERT INTO pickup_points (id, address) VALUES (29, '125703, г. Лесной, ул. Партизанская, 49');
INSERT INTO pickup_points (id, address) VALUES (30, '625283, г. Лесной, ул. Победы, 46');
INSERT INTO pickup_points (id, address) VALUES (31, '614753, г. Лесной, ул. Полевая, 35');
INSERT INTO pickup_points (id, address) VALUES (32, '426030, г. Лесной, ул. Маяковского, 44');
INSERT INTO pickup_points (id, address) VALUES (33, '450375, г. Лесной ул. Клубная, 44');
INSERT INTO pickup_points (id, address) VALUES (34, '625560, г. Лесной, ул. Некрасова, 12');
INSERT INTO pickup_points (id, address) VALUES (35, '630201, г. Лесной, ул. Комсомольская, 17');
INSERT INTO pickup_points (id, address) VALUES (36, '190949, г. Лесной, ул. Мичурина, 26');

-- Order statuses
CREATE TABLE order_statuses (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO order_statuses (id, name) VALUES (1, 'New'), (2, 'Completed');

-- Orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article VARCHAR(50) NOT NULL UNIQUE,
    order_date DATE DEFAULT NULL,
    delivery_date DATE DEFAULT NULL,
    user_id INT DEFAULT NULL,
    pickup_point_id INT NOT NULL,
    status_id INT NOT NULL,
    pickup_code VARCHAR(20) DEFAULT NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_orders_pickup FOREIGN KEY (pickup_point_id) REFERENCES pickup_points(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_status FOREIGN KEY (status_id) REFERENCES order_statuses(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (1, 'ORD-001', '2025-02-27', '2025-04-20', 7, 1, 2, '901');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (2, 'ORD-002', '2024-09-28', '2025-04-21', 8, 11, 2, '902');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (3, 'ORD-003', '2025-03-21', '2025-04-22', 9, 2, 2, '903');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (4, 'ORD-004', '2025-02-20', '2025-04-23', 10, 11, 2, '904');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (5, 'ORD-005', '2025-03-17', '2025-04-24', 7, 2, 2, '905');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (6, 'ORD-006', '2025-03-01', '2025-04-25', 8, 15, 2, '906');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (7, 'ORD-007', NULL, '2025-04-26', 9, 3, 2, '907');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (8, 'ORD-008', '2025-03-31', '2025-04-27', 10, 19, 1, '908');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (9, 'ORD-009', '2025-04-02', '2025-04-28', 9, 5, 1, '909');
INSERT INTO orders (id, article, order_date, delivery_date, user_id, pickup_point_id, status_id, pickup_code)
VALUES (10, 'ORD-010', '2025-04-03', '2025-04-29', 10, 19, 1, '910');

-- Order items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_order_product (order_id, product_id)
) ENGINE=InnoDB;
INSERT INTO order_items (order_id, product_id, quantity) VALUES (1, 1, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (1, 2, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (2, 3, 1);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (2, 4, 1);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (3, 5, 10);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (3, 6, 10);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (4, 7, 5);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (4, 8, 4);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (5, 9, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (5, 10, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (6, 1, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (6, 2, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (7, 3, 1);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (7, 4, 1);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (8, 5, 10);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (8, 6, 10);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (9, 7, 5);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (9, 8, 4);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (10, 9, 2);
INSERT INTO order_items (order_id, product_id, quantity) VALUES (10, 10, 2);