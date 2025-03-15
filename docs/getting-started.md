# Getting Started with Symfony Bref Starter

This guide will help you set up and run your Symfony Bref Starter project locally and deploy it to AWS Lambda.

## Prerequisites

You need:

- Docker and Docker Compose installed
- AWS Account with billing enabled
- AWS CLI installed and configured
- Serverless Framework CLI installed locally (`npm install -g serverless`) - Required for deployments

All other dependencies (PHP, Node.js, Yarn, etc.) are included in the Docker environment.

## Local Development Setup

### Quick Setup (Recommended for First Time)

1. Clone the repository:
   ```shell
   git clone https://github.com/livetechhelper/symfony-bref-starter.git YOUR_PROJECT
   cd YOUR_PROJECT
   ```

2. Make the startup script executable:
   ```shell
   chmod +x bin/startup.sh
   ```

3. Run the startup script:
   ```shell
   ./bin/startup.sh
   ```

The startup script automates the entire setup process:
- Creates .env.local from .env.example
- Starts Docker containers
- Installs PHP dependencies via Composer
- Installs Node.js dependencies via Yarn
- Builds frontend assets
- Sets up the database
- Clears caches

4. View the project at [http://localhost:8011](http://localhost:8011/)

### Manual Setup (Alternative Method)

If you prefer to set up the project manually or need to understand the setup process, here are the steps:

1. Create your local environment file:
   ```shell
   cp .env.example .env.local
   ```

2. Start the Docker environment:
   ```shell
   docker compose up -d
   ```

3. Install dependencies and build assets:
   ```shell
   # Enter the development container
   docker exec -ti symfony_bref_starter_dev_php bash
   cd /var/task

   # Install dependencies
   composer install
   yarn install
   yarn dev
   ```

## Development Workflow

After initial setup, here's how to work with the project:

### Asset Management

For frontend development, you can use these commands inside the dev container:

```shell
# Watch for changes during development
docker exec -ti symfony_bref_starter_dev_php bash -c "cd /var/task && yarn watch"

# Build for production
docker exec -ti symfony_bref_starter_dev_php bash -c "cd /var/task && yarn build"
```

### Running Commands

All commands should be run inside the development container:

```shell
# Enter the container
docker exec -ti symfony_bref_starter_dev_php bash
cd /var/task

# Common Symfony commands
php bin/console cache:clear
php bin/console make:controller
php bin/console make:entity
php bin/console doctrine:migrations:migrate
```

### Queue Worker

The application includes a queue worker that simulates AWS Lambda's queue processing environment:

- The worker runs in a separate container (`queue_worker`)
- It processes messages from the async transport
- Environment variables match the Lambda environment
- View logs with: `docker compose logs -f queue_worker`

To send test messages to the queue:

```shell
docker exec -ti symfony_bref_starter_dev_php bash
cd /var/task
php bin/console messenger:send-test
```

### Testing

Run tests inside the development container:

```shell
# Run full test suite
php bin/phpunit

# Run specific test
php bin/phpunit tests/path/to/test
```

## Environment Configuration

The `.env.local` file should contain your local configuration. Key variables:

```dotenv
# Application
APP_ENV=dev
APP_DEBUG=true
DATABASE_URL="mysql://admin:password@127.0.0.1:3311/symfony_bref_starter_dev"

# Queue configuration
MESSENGER_TRANSPORT_DSN=doctrine://default

# MySQL Container Configuration
MYSQL_ROOT_PASSWORD=password
MYSQL_DATABASE=symfony_bref_starter_dev
MYSQL_USER=admin
MYSQL_PASSWORD=password
```

## Deployment

Deployments are handled using the Serverless Framework:

```shell
# Deploy to development
serverless deploy --stage dev

# Deploy to production
serverless deploy --stage prod
```

Note: Deployments should be run from your local machine (not inside Docker) as they need access to your AWS credentials.

## Next Steps

- [AWS Configuration Guide](aws-setup.md) - Set up your AWS environment
- [Security Configuration](security.md) - Configure authentication and security
- [Monitoring Guide](monitoring.md) - Set up monitoring and logging
- [Performance Guide](performance.md) - Optimize your application

## Troubleshooting

### Common Issues

1. **Port conflicts**: If port 8011 is already in use, modify the port in `docker-compose.yml`.

2. **Permission issues**: If you encounter permission problems:
   ```shell
   sudo chown -R $(id -u):$(id -g) .
   ```

3. **Container access**: If you can't access the container:
   ```shell
   # Restart containers
   docker compose down
   docker compose up -d
   ```

4. **Cache issues**: Clear all caches:
   ```shell
   # Inside the dev container
   php bin/console cache:clear
   yarn cache clean
   ```

5. **Queue worker issues**: If messages aren't being processed:
   ```shell
   # Check queue worker logs
   docker compose logs -f queue_worker
   
   # Restart queue worker
   docker compose restart queue_worker
   ```

6. **Database issues**: If you need to reset the database:
   ```shell
   # Inside the dev container
   php bin/console doctrine:database:drop --force
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

### Getting Help

- Check the [Troubleshooting Guide](troubleshooting.md)
- Review [AWS Documentation](https://docs.aws.amazon.com/lambda/latest/dg/lambda-php.html)
- Visit [Bref Documentation](https://bref.sh/docs/) 