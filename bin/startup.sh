#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting Symfony Bref Starter setup..."

# Check if .env.local exists, if not create it
if [ ! -f .env.local ]; then
    echo "📝 Creating .env.local..."
    cp .env.example .env.local
fi

# Start Docker containers
echo "🐳 Starting Docker containers..."
docker compose up -d mysql dev_php

# Function to check if a service is running
check_service_running() {
    local service=$1
    local max_attempts=$2
    local attempt=1
    
    echo "⏳ Waiting for $service service to be ready..."
    while [ $attempt -le $max_attempts ]; do
        if docker compose ps $service | grep -q "Up"; then
            echo "✅ $service is running"
            return 0
        fi
        echo "⏳ Waiting for $service to be ready (attempt $attempt/$max_attempts)..."
        sleep 3
        attempt=$((attempt+1))
    done
    
    echo "❌ $service failed to start within expected time"
    return 1
}

# Wait for mysql to be ready
if ! check_service_running mysql 10; then
    echo "❌ Failed to start MySQL. Exiting."
    exit 1
fi

# Wait for database to be ready (connection test)
echo "⏳ Waiting for database to accept connections..."
MAX_ATTEMPTS=10
ATTEMPT=1
while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    if docker compose exec mysql mysqladmin ping -h localhost --silent; then
        echo "✅ Database is accepting connections"
        break
    fi
    echo "⏳ Waiting for database connection (attempt $ATTEMPT/$MAX_ATTEMPTS)..."
    sleep 3
    ATTEMPT=$((ATTEMPT+1))
    
    if [ $ATTEMPT -gt $MAX_ATTEMPTS ]; then
        echo "❌ Database failed to accept connections within expected time"
        echo "⚠️ Continuing anyway, but some operations might fail"
    fi
done

# Wait for dev_php to be ready
if ! check_service_running dev_php 5; then
    echo "❌ dev_php service is not running. Starting it now..."
    docker compose up -d dev_php
    
    if ! check_service_running dev_php 5; then
        echo "❌ Failed to start dev_php service. Check docker logs for details:"
        docker compose logs dev_php
        exit 1
    fi
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies and assets..."
docker compose exec -w /var/task dev_php composer install

# Check if previous command was successful
if [ $? -ne 0 ]; then
    echo "❌ Failed to install PHP dependencies"
    exit 1
fi

docker compose exec -w /var/task dev_php bin/console assets:install public --symlink

# Install Node.js dependencies and build assets
echo "🎨 Installing and building frontend assets..."
docker compose exec -w /var/task dev_php yarn install
docker compose exec -w /var/task dev_php yarn dev

# Create and setup database
echo "🗄️ Setting up database..."
docker compose exec -w /var/task dev_php php bin/console doctrine:database:create --if-not-exists
docker compose exec -w /var/task dev_php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Check if database has users, if not load fixtures
echo "🌱 Checking if database needs seeding..."
if ! docker compose exec -w /var/task dev_php php bin/console doctrine:query:sql "SELECT 1 FROM user LIMIT 1" > /dev/null 2>&1; then
    echo "🌱 Database is empty. Loading fixtures..."
    docker compose exec -w /var/task dev_php php bin/console doctrine:fixtures:load --no-interaction
    echo "✅ Database seeded successfully!"
else
    echo "✅ Database already has data. Skipping fixtures."
fi

# Start web and queue worker
echo "🌐 Starting web server and queue worker..."
docker compose up -d web queue_worker

# Check if web service started successfully
if ! check_service_running web 5; then
    echo "⚠️ Warning: Web service might not have started properly"
fi

# Check if queue worker started successfully
echo "📨 Checking queue worker status..."
if ! check_service_running queue_worker 5; then
    echo "⚠️ Warning: Queue worker might not have started properly"
fi

# Additional verification for queue worker
sleep 3
if ! docker compose logs queue_worker | grep -q "Consuming messages from"; then
    echo "⚠️ Warning: Queue worker might not be consuming messages. Check logs with: docker compose logs queue_worker"
else
    echo "✅ Queue worker is consuming messages"
fi

# Clear cache
echo "🧹 Clearing cache..."
docker compose exec -w /var/task dev_php php bin/console cache:clear

echo "✅ Setup complete! Your application is running at http://localhost:8011"
echo "ℹ️ Queue worker is running in background"
echo "🔍 Check the logs with: docker compose logs -f"
echo ""
echo "To verify the queue worker is running correctly, check the logs with:"
echo "docker compose logs queue_worker" 