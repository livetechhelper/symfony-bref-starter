# AWS Configuration Guide

This guide explains how to set up your AWS environment for deploying the Symfony Bref application.

## Prerequisites

- AWS Account with billing enabled
- AWS CLI installed and configured
- Serverless Framework CLI installed
- Basic understanding of AWS services (Lambda, RDS, VPC)

## Initial AWS Setup

1. Install AWS CLI and configure credentials:
   ```shell
   aws configure
   ```
   Enter your AWS Access Key ID, Secret Access Key, default region, and output format.

2. Create an IAM User for Deployment:
   - Go to AWS IAM Console
   - Create a new user with programmatic access
   - Attach the following policies:
     - `AWSLambdaFullAccess`
     - `AmazonRDSFullAccess`
     - `AmazonVPCFullAccess`
     - `AmazonS3FullAccess`
     - `CloudWatchLogsFullAccess`
     - `AmazonSQSFullAccess`

## VPC Configuration

Your Lambda functions should run in a VPC for RDS access. The `serverless.yml` includes VPC configuration:

```yaml
provider:
  vpc:
    securityGroupIds:
      - ${self:custom.vpc.securityGroupIds}
    subnetIds:
      - ${self:custom.vpc.subnetIds}
```

### Creating Required Resources

1. Create a VPC:
   ```shell
   aws ec2 create-vpc --cidr-block 10.0.0.0/16
   ```

2. Create Subnets:
   ```shell
   aws ec2 create-subnet --vpc-id <vpc-id> --cidr-block 10.0.1.0/24
   aws ec2 create-subnet --vpc-id <vpc-id> --cidr-block 10.0.2.0/24
   ```

3. Create Security Groups:
   ```shell
   aws ec2 create-security-group --group-name lambda-sg --description "Security group for Lambda functions"
   ```

## Database Setup

1. Create an RDS instance:
   - Use the AWS RDS Console
   - Choose MySQL 8.0
   - Select the VPC created above
   - Configure security group access

2. Update Environment Variables:
   ```dotenv
   DATABASE_URL="mysql://user:pass@your-rds-endpoint:3306/dbname"
   ```

## S3 Configuration

1. Create an S3 bucket for assets:
   ```shell
   aws s3 mb s3://your-bucket-name
   ```

2. Configure bucket policy:
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Sid": "PublicReadGetObject",
         "Effect": "Allow",
         "Principal": "*",
         "Action": "s3:GetObject",
         "Resource": "arn:aws:s3:::your-bucket-name/*"
       }
     ]
   }
   ```

## SQS Setup

1. Create SQS queues:
   ```shell
   aws sqs create-queue --queue-name your-queue-name
   aws sqs create-queue --queue-name your-queue-name-dlq
   ```

2. Update queue URLs in `serverless.yml`:
   ```yaml
   custom:
     queueUrl: ${cf:your-stack-name.QueueUrl}
     dlqUrl: ${cf:your-stack-name.DLQUrl}
   ```

## Environment Variables

Update your `.env.prod` file with production values:

```dotenv
APP_ENV=prod
APP_SECRET=your-production-secret
DATABASE_URL=mysql://user:pass@your-rds-endpoint:3306/dbname
AWS_BUCKET=your-bucket-name
SQS_QUEUE_URL=https://sqs.region.amazonaws.com/account-id/queue-name
```

## Next Steps

- [Deployment Guide](deployment.md) - Deploy your application
- [Monitoring Guide](monitoring.md) - Set up monitoring and alerts
- [Troubleshooting](troubleshooting.md) - Common issues and solutions 