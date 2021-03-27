web: vendor/bin/heroku-php-apache2 public/
worker1: php artisan queue:restart && php artisan queue:work --tries=3
worker2: php artisan queue:restart && php artisan queue:work --tries=3
worker3: php artisan queue:restart && php artisan queue:work --tries=3
worker4: php artisan queue:restart && php artisan queue:work --tries=3
