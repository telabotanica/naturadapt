# Database migrations
./bin/console doctrine:migrations:migrate

# Fixtures
./bin/console doctrine:fixtures:load --no-interaction

# Front-End
npm install
npm run build
