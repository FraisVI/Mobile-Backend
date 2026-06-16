[Database structure](https://www.figma.com/)

`docker exec -i mariadb_portfolio_backend mysql --user root --password=root laravel < sample_dump.sql` - load dump

На проде **не** используются следующие файлы:
* laravel/public
* Dockerfile
* docker-compose.yml
* my.cnf


`docker compose up --build` - запуск для dev

`php artisan route:list` - выводит список всех маршрутов
