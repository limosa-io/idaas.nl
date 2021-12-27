# git pull origin master
composer install --no-interaction --prefer-dist --optimize-autoloader
# echo "" | sudo -S service php7.2-fpm reload

php artisan migrate --force

cd frontend
yarn install && yarn run build
cd ..

php artisan view:cache
php artisan config:cache
php artisan route:cache

echo "" | sudo -S service php7.2-fpm reload