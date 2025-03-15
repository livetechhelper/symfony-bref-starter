# Symfony Bref Starter

A production-ready starter template for building serverless PHP applications using Symfony and AWS Lambda via Bref. This template provides a complete foundation for developing, deploying, and maintaining serverless PHP applications with enterprise-grade features and best practices.

## 🌟 Features

- **Serverless Architecture**:
  - Built on AWS Lambda using [Bref](https://bref.sh/)
  - Automatic scaling and pay-per-use pricing
  - Zero server maintenance
  - High availability across multiple AZs

- **Modern PHP Stack**:
  - Symfony 7.2 framework
  - PHP 8.3 with JIT compilation
  - Composer 2.x dependency management
  - PSR standards compliance

- **Authentication & Security**:
  - Complete user management system
  - Secure password handling
  - CSRF protection
  - AWS security best practices
  - See [Security Guide](docs/security.md)

- **Theme System**:
  - Light/dark mode with persistent settings
  - Tailwind CSS for responsive design
  - Modern UI components
  - Customizable themes

- **Serverless Infrastructure**:
  - API Gateway for HTTP routing
  - RDS for scalable database
  - DynamoDB for sessions and cache
  - SQS for message queues
  - CloudFront for global asset delivery
  - See [AWS Setup Guide](docs/aws-setup.md)

- **Developer Experience**:
  - Docker-based local development
  - Hot-reloading for assets
  - Symfony debug toolbar
  - Comprehensive logging
  - Detailed documentation

## 📋 Prerequisites

Before you begin, ensure you have:

- Docker and Docker Compose installed
- AWS Account with billing enabled
- AWS CLI installed and configured

All other dependencies (PHP, Node.js, Yarn, Serverless Framework) are included in the Docker environment.

See [Getting Started Guide](docs/getting-started.md) for detailed setup instructions.

## 🚀 Quick Start

1. Clone the repository:
   ```bash
   git clone https://github.com/livetechhelper/symfony-bref-starter.git
   cd symfony-bref-starter
   ```

2. Make the startup script executable:
   ```bash
   chmod +x bin/startup.sh
   ```

3. Run the startup script to set up your environment:
   ```bash
   ./bin/startup.sh
   ```
   This script will:
   - Create your .env.local file
   - Start Docker containers
   - Install PHP dependencies
   - Install Node.js dependencies
   - Build frontend assets
   - Set up the database
   - Clear caches

4. Access the application:
   - Website: [http://localhost:8011](http://localhost:8011)
   - Symfony debug toolbar enabled in dev environment

For manual setup instructions and development workflow, see the [Getting Started Guide](docs/getting-started.md).

## 📚 Documentation

### Core Guides
- [Getting Started Guide](docs/getting-started.md)
  - Local development setup
  - Docker environment
  - Initial configuration
  - First deployment

- [AWS Configuration](docs/aws-setup.md)
  - IAM roles and permissions
  - VPC setup
  - RDS configuration
  - CloudFront distribution
  - Parameter Store setup

- [Deployment Guide](docs/deployment.md)
  - Deployment process
  - Environment configuration
  - Database migrations
  - Asset management
  - Rollback procedures

### Advanced Topics
- [Security Configuration](docs/security.md)
  - Authentication setup
  - Password management
  - CSRF protection
  - AWS security
  - Session handling

- [Monitoring Guide](docs/monitoring.md)
  - CloudWatch metrics
  - X-Ray tracing
  - Error tracking
  - Health checks
  - Performance monitoring

- [Performance Tuning](docs/performance.md)
  - Cold start optimization
  - Database optimization
  - Caching strategies
  - Asset delivery
  - Lambda configuration

## 🏗️ Architecture

The application uses a modern serverless architecture:

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  CloudFront │ ──▶ │ API Gateway │ ──▶ │   Lambda    │
└─────────────┘     └─────────────┘     └─────────────┘
                                              │
                                              ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  DynamoDB   │ ◀── │    RDS      │ ◀── │    SQS      │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Key Components

- **Application Layer**: PHP 8.3 + Symfony 7.2
  - Handles HTTP requests via Lambda
  - Processes background jobs
  - Manages application logic

- **Database Layer**: MySQL 8.0 on Amazon RDS
  - Stores application data
  - Automatic backups
  - Multi-AZ deployment option

- **Caching Layer**: DynamoDB
  - Session storage
  - Application cache
  - Distributed locking

- **Message Queue**: Amazon SQS
  - Asynchronous processing
  - Event-driven architecture
  - Dead letter queues

- **Content Delivery**: CloudFront CDN
  - Global asset delivery
  - SSL/TLS termination
  - DDoS protection

## 🚀 Architecture

### Message Queue System

This project uses Symfony Messenger for handling asynchronous operations. The setup is specifically designed to mirror AWS Lambda behavior in the local development environment:

- **Queue Worker Container**: Uses `bref/php-83` image to simulate the Lambda environment for processing messages
- **Environment Consistency**: Ensures environment variables and context match AWS Lambda
- **Transport**: Uses Doctrine as message transport (configurable in `.env`)

#### Message Types

Messages are organized using interfaces:

- `MessageInterface`: Base interface for all messages
- `AsyncMessageInterface`: For messages that should be processed by the queue worker
- `SyncMessageInterface`: For messages that need immediate processing

#### Why This Setup?

When deploying to AWS Lambda, the environment for processing messages (queue worker) is different from the web environment:

1. Different environment variables are available
2. Different PHP settings may apply
3. Different services may be accessible

By using a separate container with the Bref runtime for the queue worker, we:
- Catch environment-related issues early
- Ensure consistent behavior between local and AWS environments
- Make it easier to debug queue-related issues

#### Best Practices

1. Always implement either `AsyncMessageInterface` or `SyncMessageInterface`
2. Use async messages for:
   - Operations that can be delayed
   - Background processing
   - Operations that might fail and need retry
3. Use sync messages for:
   - Operations that must complete before the response
   - Operations that affect the user's immediate experience

## 🛠️ Development

### Local Development

The development environment closely matches production:

```bash
# Start containers
docker compose up -d

# Access container shell
docker compose exec -w /var/task dev_php bash

# Run Symfony commands
php bin/console cache:clear
```

### Asset Management

```bash
# Watch for changes
yarn watch

# Build for production
yarn build
```

### Testing

```bash
# Run test suite
docker compose exec -w /var/task dev_php php bin/phpunit

# Run specific test
docker compose exec -w /var/task dev_php php bin/phpunit tests/path/to/test
```

## 🚀 Deployment

1. Configure AWS credentials:
   ```bash
   aws configure
   ```

2. Deploy to development:
   ```bash
   serverless deploy --stage dev
   ```

3. Deploy to production:
   ```bash
   serverless deploy --stage prod
   ```

See the [Deployment Guide](docs/deployment.md) for complete instructions.

## 🔒 Security Features

- User authentication system
- Password reset functionality
- CSRF protection
- Secure session handling
- AWS security best practices
- See [Security Guide](docs/security.md)

## 📊 Monitoring Features

- CloudWatch integration
- X-Ray distributed tracing
- Custom metrics
- Error tracking
- Health checks
- See [Monitoring Guide](docs/monitoring.md)

## 🎯 Performance Features

- Cold start optimization
- Asset optimization
- Multi-layer caching
- Database optimization
- Lambda tuning
- See [Performance Guide](docs/performance.md)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details on:
- Code of Conduct
- Development process
- Pull request guidelines
- Coding standards

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Bref](https://bref.sh/) - PHP Runtime for AWS Lambda
- [Symfony](https://symfony.com/) - PHP Framework
- [Serverless Framework](https://www.serverless.com/) - Infrastructure as Code
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework