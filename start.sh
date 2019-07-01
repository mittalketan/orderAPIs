#!/usr/bin/env bash

red=$'\e[1;31m'
grn=$'\e[1;32m'
blu=$'\e[1;34m'
mag=$'\e[1;35m'
cyn=$'\e[1;36m'
white=$'\e[0m'

# sudo apt update
# sudo apt install -y curl

echo " $red **** Installing Pre requisites **** $white "
sudo docker-compose down && docker-compose up --build -d

echo " $grn **** Installing Dependencies **** $blu " 

docker-compose run composer install  --ignore-platform-reqs --no-interaction --no-progress --quiet

docker exec app bash -c 'chmod 777 -R /var/www/html'

docker exec app php artisan config:cache
docker exec app php artisan optimize:clear

echo " $red **** Running Migrations **** $white "
docker exec app php artisan migrate

echo " $red **** Running Unit test cases **** $white "
docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Unit

echo " $red **** Running Intergration test cases **** $white "
docker exec app php ./vendor/bin/phpunit /var/www/html/tests/Feature

exit 0