# Getting Started with Symfony Bref Starter

This guide will help you set up and run your Symfony Bref Starter project locally and deploy it to AWS Lambda.

## Prerequisites

- Docker and Docker Compose
- AWS Account with billing enabled
- AWS CLI installed and configured
- Node.js and Yarn (for asset management)
- Serverless Framework CLI (`npm install -g serverless`)

## Local Development Setup

1. Clone the repository:
   ```shell
   git clone https://github.com/livetechhelper/symfony-bref-starter.git YOUR_DIR
   cd YOUR_DIR
   ```

2. Start the Docker containers:
   ```shell
   docker compose up -d
   ```

3. Enter the development container:
   ```shell
   docker exec -ti symfony_bref_starter_dev_php bash
   cd /var/task
   ```

4. Install dependencies:
   ```shell
   composer install
   yarn install
   yarn dev
   ```

5. View the project:
   Open [http://localhost:8011/](http://localhost:8011/) in your browser.

## Development Workflow

### Running Commands

All Symfony commands should be run inside the development container. For example:

```shell
# Create a new controller
php bin/console make:controller

# Create a new entity
php bin/console make:entity

# Run database migrations
php bin/console doctrine:migrations:migrate
```

### Asset Management

The project uses Webpack Encore for asset management:

```shell
# Watch for changes and rebuild
yarn watch

# Build for production
yarn build
```

### Testing

```shell
# Run tests
php bin/phpunit

# Run specific test
php bin/phpunit tests/path/to/test
```

## Environment Configuration

1. Create a `.env.local` file:
   ```shell
   cp .env .env.local
   ```

2. Configure your environment variables:
   ```dotenv
   APP_ENV=dev
   APP_SECRET=your-secret-here
   DATABASE_URL="mysql://user:pass@host:3306/dbname"
   ```

## Next Steps

- [AWS Configuration](aws-setup.md) - Set up your AWS environment
- [Database Setup](database-setup.md) - Configure your database
- [Deployment Guide](deployment.md) - Deploy your application
- [Security Configuration](security.md) - Configure authentication and authorization 