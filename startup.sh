#!/bin/bash

FOLDER="./vendor"

# Check if the folder is empty
if [ -z "$(ls -A "$FOLDER")" ]; then
  echo "vendor folder is empty so building project first"
  docker-compose run --rm dev_php sh -c "cd /var/task && composer install && yarn install && yarn dev"
  echo "creating test table in database"
  docker-compose run --rm dev_php sh -c "cd /var/task && bin/console doctrine:schema:update --force"
  echo "seeding table with test data"
  docker-compose run --rm dev_php sh -c "cd /var/task && bin/console doctrine:fixtures:load --no-interaction"
else
   echo "vendor folder not empty, so not installing everything"
fi

echo "Running docker-compose"
docker-compose up -d
echo "All containers should now be running, you can view your app at http://localhost:8011"