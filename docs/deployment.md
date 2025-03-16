# Deployment Guide

This guide explains how to deploy your Symfony Bref application to AWS Lambda.

## Prerequisites

Ensure you have completed the steps in:
- [Getting Started Guide](getting-started.md)
- [AWS Configuration Guide](aws-setup.md)

## Pre-deployment Checklist

1. Environment Configuration:
   - Verify `.env.prod` exists with production values
   - Check all required environment variables are set
   - Ensure sensitive values are properly secured

2. Database:
   - Run migrations locally to verify they work
   - Backup production database if updating an existing deployment

3. Assets:
   - Build production assets:
     ```shell
     yarn build
     ```
   - Verify assets are properly compiled in `public/build`

## Deployment Process

1. Install Serverless plugins:
   ```shell
   serverless plugin install -n serverless-lift
   serverless plugin install -n serverless-vpc-plugin
   ```

2. Deploy to AWS:
   ```shell
   # Deploy to development
   serverless deploy --stage dev

   # Deploy to production
   serverless deploy --stage prod
   ```

3. First-time Database Setup:
   ```shell
   # Run migrations on production
   serverless invoke --function console --data "doctrine:migrations:migrate --no-interaction"
   ```

## Post-deployment Verification

1. Check Lambda Functions:
   - Verify functions appear in AWS Console
   - Test function URLs
   - Check CloudWatch logs for errors

2. Verify Database:
   - Confirm migrations applied successfully
   - Check database connectivity

3. Test Asset Delivery:
   - Verify static assets load from S3
   - Check CDN configuration if used

4. Monitor Application:
   - Check CloudWatch metrics
   - Verify error reporting
   - Test key functionality

## Rollback Procedure

If deployment fails or issues are discovered:

1. Rollback to Previous Version:
   ```shell
   serverless rollback --timestamp TIMESTAMP
   ```

2. Database Rollback:
   ```shell
   serverless invoke --function console --data "doctrine:migrations:migrate prev"
   ```

## Common Issues

### Cold Start Performance
- Enable provisioned concurrency for critical functions
- Optimize dependency loading
- Use appropriate memory settings

### Database Connectivity
- Verify VPC configuration
- Check security group rules
- Confirm database credentials

### Asset Loading
- Verify S3 bucket permissions
- Check asset paths in templates
- Confirm CloudFront configuration

## Monitoring Deployment

1. CloudWatch Logs:
   ```shell
   serverless logs --function app --tail
   ```

2. View Stack Events:
   ```shell
   serverless info
   ```

## Production Optimizations

1. Enable HTTP Keep-Alive:
   ```yaml
   provider:
     httpApi:
       payload: '2.0'
       keepAlive: true
   ```

2. Configure Provisioned Concurrency:
   ```yaml
   functions:
     app:
       provisionedConcurrency: 5
   ```

3. Optimize Memory Settings:
   ```yaml
   provider:
     memorySize: 1024
   ```

## Next Steps

- [Monitoring Guide](monitoring.md) - Set up monitoring and alerts
- [Performance Tuning](performance.md) - Optimize your application
- [Security Best Practices](security.md) - Secure your deployment

## GitHub Actions Setup

1. **Create Environment Secrets**:
   - Go to your GitHub repository.
   - Navigate to `Settings` > `Secrets and variables` > `Actions`.
   - Add the following secrets:
     - `AWS_ACCESS_KEY_ID`: Your AWS access key ID.
     - `AWS_SECRET_ACCESS_KEY`: Your AWS secret access key.
     - `AWS_REGION`: Your AWS region (e.g., `us-east-1`).

2. **Deployment Workflow**:
   - The GitHub Actions workflow is defined in `.github/workflows/deploy.yml`.
   - It automatically deploys to the development environment on pushes to the `develop` branch.
   - To deploy to production, trigger the workflow manually and specify `prod` as the environment.

## GitLab CI/CD Setup

1. **Create Environment Variables**:
   - Go to your GitLab project.
   - Navigate to `Settings` > `CI / CD` > `Variables`.
   - Add the following variables:
     - `AWS_ACCESS_KEY_ID`: Your AWS access key ID.
     - `AWS_SECRET_ACCESS_KEY`: Your AWS secret access key.
     - `AWS_REGION`: Your AWS region (e.g., `us-east-1`).

2. **Deployment Pipeline**:
   - The GitLab CI/CD pipeline is defined in `.gitlab-ci.yml`.
   - It automatically deploys to the development environment on pushes to the `develop` branch.
   - To deploy to production, trigger the pipeline manually from the GitLab interface.

## Additional Configuration

- Ensure your AWS IAM user has the necessary permissions as specified in `docs/iam_role.md`.
- Update `serverless.yml` with your AWS account ID, region, and service name.

This guide ensures you can easily configure and deploy your application using either GitHub Actions or GitLab CI/CD. 