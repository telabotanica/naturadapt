# NaturAdapt

## Install

Clone the repository, install _composer_ then run:
```bash
composer install
```

Copy .env to .env.local and change the settings, particulary:
- ```APP_ENV```
- ```DATABASE_URL```
- ```DATABASE_PREFIX```

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

Activate a user:
```bash
php bin/console user:activate <user-email>
```

Deactivate a user:
```bash
php bin/console user:deactivate <user-email>
```

Give ROLE_ADMIN to a user:
```bash
php bin/console user:set-admin <user-email>
```

## FAQ

### How to force https ?

Edit your .env and a ```SECURE_SCHEME``` variable with ```https```

### How to handle proxies ?

You can add ```TRUSTED_PROXIES``` to your .env.

Or you can add a ```TRUST_ALL=1``` to always forward the ```HEADER_X_FORWARDED_*``` headers, as mentionned on https://symfony.com/doc/current/deployment/proxies.html.
