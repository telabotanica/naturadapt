# NaturAdapt

## Install

Clone the repository, install _composer_ then run:
```bash
composer install
```

Copy .env to .env.local and change the settings, particulary:
- APP_ENV
- DATABASE_URL
- DATABASE_PREFIX

If necessary, create the DB:
```bash
php bin/console doctrine:database:create
```

Create the tables:
```bash
php bin/console doctrine:migrations:migrate
```

## Fixtures

Fill the plateform with _Lorem Ipsum_:
```bash
php bin/console doctrine:fixtures:load
```


## Toolbox

Give ROLE_ADMIN to a user:
```bash
php bin/console app:set-admin <user-email>
```
