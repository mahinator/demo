
  1. Подключение и обновление:
    ssh masha@<NEW_IP>
    sudo apt update && sudo apt upgrade -y



    sudo rm -f /var/www/html/index.html   !!!!



  4. Выдача прав:
    sudo mkdir -p /var/www/html/uploads
    sudo chown -R www-data:www-data /var/www/html
    sudo chmod -R 777 /var/www/html

  5. Создание пользователя СУБД:
  Запустите  sudo mysql  и введите:
    CREATE USER 'student'@'localhost' IDENTIFIED BY 'password';
    CREATE USER 'student'@'%' IDENTIFIED BY 'password';
    GRANT ALL PRIVILEGES ON demo.* TO 'student'@'localhost';
    GRANT ALL PRIVILEGES ON demo.* TO 'student'@'%';
    FLUSH PRIVILEGES;
    EXIT;

  6. Импорт базы данных:
    sudo mysql < /var/www/html/script.sql

  7. Разрешение внешних подключений (для Workbench) и перезапуск:
    sudo sed -i 's/^bind-address.*/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
    sudo systemctl restart mysql
    sudo systemctl restart apache2

cd /var/www/html/ (перейти в папку файлов сайта. Здесь создаешь файлы)
rm /var/www/html/index.html (один раз пишешь, чтобы удалить бесполезный файл)
nano имя_файла.php (Когда ты в папке сайта, создаешь файлы, меняешь их через эту команду. Её надо запомнить. Когда ввел команду и вставил код в файл, нажимаешь Y -> Enter чтобы сохранить и выйти из файла)



