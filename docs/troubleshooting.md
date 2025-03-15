# Troubleshooting Guide

This guide covers common issues and their solutions when working with the Symfony Bref Starter.

## Local Development Issues

### Docker Issues

#### Container Won't Start
```shell
# Check container logs
docker compose logs

# Restart containers
docker compose down
docker compose up -d
```

#### Permission Issues
```shell
# Fix file permissions
sudo chown -R $(id -u):$(id -g) .

# If using WSL2 on Windows
sudo chown -R $(id -u):$(id -g) /var/task
```

#### Port Conflicts
If port 8011 is already in use:
1. Edit `docker-compose.yml`
2. Change the port mapping (e.g., "8012:8000")
3. Restart containers

### Database Issues

#### Can't Connect to Database
1. Check database container is running:
   ```shell
   docker compose ps database
   ```
2. Verify database credentials in `.env.local`
3. Try connecting directly:
   ```shell
   docker compose exec database mysql -uapp -papp app
   ```

#### Migration Errors
```shell
# Reset database
docker compose exec -w /var/task dev_php php bin/console doctrine:database:drop --force
docker compose exec -w /var/task dev_php php bin/console doctrine:database:create
docker compose exec -w /var/task dev_php php bin/console doctrine:migrations:migrate --no-interaction
```

### Queue Worker Issues

#### Messages Not Being Processed
1. Check queue worker logs:
   ```shell
   docker compose logs -f queue_worker
   ```

2. Verify transport configuration:
   ```shell
   docker compose exec -w /var/task dev_php php bin/console debug:messenger
   ```

3. Restart queue worker:
   ```shell
   docker compose restart queue_worker
   ```

#### Queue Worker Environment
The queue worker simulates Lambda environment variables. If you need to debug:
```shell
docker compose exec queue_worker env | grep AWS
```

### Asset Issues

#### Assets Not Building
1. Clear Yarn cache:
   ```shell
   docker compose exec -w /var/task dev_php yarn cache clean
   ```

2. Remove node_modules and reinstall:
   ```shell
   docker compose exec -w /var/task dev_php rm -rf node_modules
   docker compose exec -w /var/task dev_php yarn install
   ```

#### Hot Reload Not Working
1. Check Webpack is running:
   ```shell
   docker compose exec -w /var/task dev_php yarn watch
   ```

2. Verify browser console for errors

## Deployment Issues

### Serverless Framework

#### AWS Credentials
1. Check AWS credentials are configured:
   ```shell
   aws configure list
   ```

2. Verify AWS profile:
   ```shell
   aws sts get-caller-identity
   ```

#### Deployment Fails

1. Check CloudFormation logs in AWS Console

2. Common issues:
   - IAM permissions missing
   - Resource limits reached
   - Invalid configuration

3. Roll back deployment:
   ```shell
   serverless remove --stage dev
   ```

### Lambda Issues

#### Cold Start Performance
1. Enable provisioned concurrency in `serverless.yml`:
   ```yaml
   functions:
     app:
       provisionedConcurrency: 5
   ```

2. Optimize dependencies:
   ```shell
   composer install --no-dev --optimize-autoloader
   ```

#### Function Timeouts
1. Check CloudWatch logs
2. Increase timeout in `serverless.yml`
3. Consider breaking into smaller functions

### Database Connection Issues

#### RDS Connection Timeout
1. Check security groups
2. Verify VPC configuration
3. Test connection from Lambda console

#### Migration Issues
Run migrations through Lambda:
```shell
serverless invoke --function console --data "doctrine:migrations:migrate --no-interaction"
```

## Performance Issues

### Slow Response Times

1. Enable Xdebug profiling:
   ```shell
   docker compose exec -w /var/task dev_php php -d xdebug.mode=profile your-script.php
   ```

2. Use Symfony Profiler in dev environment

3. Check CloudWatch metrics in production

### Memory Issues

1. Monitor memory usage:
   ```shell
   docker compose exec -w /var/task dev_php php -d memory_limit=-1 bin/console cache:clear
   ```

2. Adjust Lambda memory in `serverless.yml`

## Getting Help

### Debug Information

Gather debug information:
```shell
# PHP version and extensions
docker compose exec -w /var/task dev_php php -v
docker compose exec -w /var/task dev_php php -m

# Symfony version
docker compose exec -w /var/task dev_php php bin/console --version

# Composer dependencies
docker compose exec -w /var/task dev_php composer show
```

### Useful Commands

```shell
# Clear all caches
docker compose exec -w /var/task dev_php php bin/console cache:clear
docker compose exec -w /var/task dev_php php bin/console cache:warmup

# Check Symfony requirements
docker compose exec -w /var/task dev_php php bin/console about

# Validate configuration
docker compose exec -w /var/task dev_php php bin/console lint:yaml config/
```

### External Resources

- [Symfony Documentation](https://symfony.com/doc)
- [Bref Documentation](https://bref.sh/docs/)
- [AWS Lambda Documentation](https://docs.aws.amazon.com/lambda/latest/dg/lambda-php.html)
- [Serverless Framework Documentation](https://www.serverless.com/framework/docs/) 