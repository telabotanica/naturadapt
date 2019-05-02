# Database migrations
./bin/console doctrine:migrations:migrate

# Fixtures
./bin/console doctrine:fixtures:load --no-interaction
./bin/console import:skills

# Front-End
npm install
npm run build
