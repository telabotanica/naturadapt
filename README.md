# Nos Pollinisateurs

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

## Inserting default data

The platform uses data like taxonomies to be fully functionnal. Default data can be inserted via PHP commands.

### Skills

Default skills are defined as slugs in the Command _src/Command/ImportSkillsCommand.php_

To import default Skills, run
```bash
php bin/console import:skills
```

Eventually, additionnal skills can be directly in the database.

Skills translations in the differents languages are managed via Symfony translations via the specific _skills_ domain.
ex: _translations/skills.fr.yml_

## General group

If the _env_ variable _COMMUNITY_SLUG_ is defined, the corresponding group will be defacto the "general" group, and every user registred will be by default a member of this group.

## Fixtures

Fill the platform with _Lorem Ipsum_:
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

## Indexes

Generate all indexes:
```bash
php bin/console search:reindex:all
```

Generate one index (`pages`, `discussions_messages`, `articles`, `documents`, `groups`, `members`)
```bash
php bin/console search:reindex <index>
```

## FAQ

### How to force https ?

Edit your .env and a ```SECURE_SCHEME``` variable with ```https```

### How to handle proxies ?

You can add ```TRUSTED_PROXIES``` to your .env.

Or you can add a ```TRUST_ALL=1``` to always forward the ```HEADER_X_FORWARDED_*``` headers, as mentionned on https://symfony.com/doc/current/deployment/proxies.html.

## Setup for local development
TO DO
