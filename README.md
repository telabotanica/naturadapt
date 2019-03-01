# NaturAdapt

## Install

Clone the repository

```bash
composer install
```

Copy .env to .env.local and change the settings

Create the DB and the tables :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Fill the default data :
```bash
php bin/console app:init
```
