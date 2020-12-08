# Database migrations
./bin/console doctrine:migrations:sync-metadata-storage --no-interaction
./bin/console doctrine:migrations:migrate --no-interaction

# Cache warmup
./bin/console cache:clear

# Fixtures
./bin/console import:skills

# Front-End
npm install
npm run build
