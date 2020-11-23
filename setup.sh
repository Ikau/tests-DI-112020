php bin/console doctrine:database:create -n
php bin/console doctrine:database:create --env=test -n
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:migrations:migrate --env=test -n
