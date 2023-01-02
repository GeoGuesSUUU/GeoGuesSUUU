# Fixture Documentation

## Load Fixture

Use the following command :
```shell
php .\bin\console doctrine:fixtures:load
```
_\* Warning ! : this command purge data in DB_


### Load Fixture without purging
```shell
php .\bin\console doctrine:fixtures:load --append
```