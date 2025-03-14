# Monitoring Guide

This guide covers monitoring and observability for your Symfony Bref application on AWS Lambda.

## CloudWatch Metrics

### 1. Lambda Function Metrics

Key metrics to monitor:

- Invocations
- Errors
- Duration
- Throttles
- ConcurrentExecutions
- IteratorAge (for stream-based functions)

Set up CloudWatch dashboard:

```yaml
resources:
  Resources:
    ApplicationDashboard:
      Type: AWS::CloudWatch::Dashboard
      Properties:
        DashboardName: ${self:service}-${sls:stage}
        DashboardBody:
          widgets:
            - type: metric
              properties:
                metrics:
                  - [ "AWS/Lambda", "Invocations", "FunctionName", "${self:service}-${sls:stage}-app" ]
                  - [ ".", "Errors", ".", "." ]
                  - [ ".", "Duration", ".", "." ]
                period: 300
                stat: "Sum"
                region: ${self:provider.region}
                title: "Lambda Metrics"
```

### 2. Custom Metrics

Send custom metrics using CloudWatch:

```php
use Aws\CloudWatch\CloudWatchClient;

$cloudWatch = new CloudWatchClient([
    'version' => 'latest',
    'region'  => $_ENV['AWS_REGION']
]);

$cloudWatch->putMetricData([
    'Namespace' => 'YourApp',
    'MetricData' => [
        [
            'MetricName' => 'LoginAttempts',
            'Value' => 1,
            'Unit' => 'Count',
            'Dimensions' => [
                ['Name' => 'Environment', 'Value' => $_ENV['APP_ENV']]
            ]
        ]
    ]
]);
```

## Logging Configuration

### 1. Monolog Setup

Configure Monolog for structured logging:

```yaml
# config/packages/monolog.yaml
monolog:
    channels: ['app', 'security']
    handlers:
        cloudwatch:
            type: service
            id: aws_logs.handler
            channels: ['!event']
            level: info
            formatter: monolog.formatter.json
```

### 2. Contextual Logging

Add context to logs:

```php
$this->logger->info('User action completed', [
    'user_id' => $user->getId(),
    'action' => 'profile_update',
    'duration_ms' => $duration,
    'request_id' => $request->headers->get('X-Request-ID')
]);
```

## Error Tracking

### 1. Exception Handling

Configure global exception handler:

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logger->error('Uncaught exception', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
```

### 2. CloudWatch Alarms

Set up alarms for critical errors:

```yaml
resources:
  Resources:
    ErrorAlarm:
      Type: AWS::CloudWatch::Alarm
      Properties:
        AlarmName: ${self:service}-${sls:stage}-errors
        AlarmDescription: Alert on high error rate
        MetricName: Errors
        Namespace: AWS/Lambda
        Dimensions:
          - Name: FunctionName
            Value: ${self:service}-${sls:stage}-app
        Period: 300
        EvaluationPeriods: 1
        Threshold: 5
        ComparisonOperator: GreaterThanThreshold
        Statistic: Sum
        TreatMissingData: notBreaching
```

## Performance Monitoring

### 1. X-Ray Integration

Enable AWS X-Ray tracing:

```yaml
# serverless.yml
provider:
  tracing:
    apiGateway: true
    lambda: true
```

Configure Symfony for X-Ray:

```yaml
# config/packages/aws.yaml
aws:
    xray:
        enabled: true
```

### 2. Database Monitoring

Monitor RDS metrics:

```yaml
resources:
  Resources:
    DatabaseAlarm:
      Type: AWS::CloudWatch::Alarm
      Properties:
        AlarmName: ${self:service}-${sls:stage}-database-connections
        MetricName: DatabaseConnections
        Namespace: AWS/RDS
        Period: 300
        EvaluationPeriods: 1
        Threshold: 80
        ComparisonOperator: GreaterThanThreshold
        Statistic: Average
```

## Health Checks

### 1. Application Health

Create health check endpoint:

```php
// src/Controller/HealthController.php
#[Route('/health', name: 'health_check')]
public function check(): JsonResponse
{
    $health = [
        'status' => 'healthy',
        'timestamp' => time(),
        'checks' => [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache()
        ]
    ];

    return new JsonResponse($health);
}
```

### 2. Route53 Health Check

Configure DNS health check:

```yaml
resources:
  Resources:
    HealthCheck:
      Type: AWS::Route53::HealthCheck
      Properties:
        HealthCheckConfig:
          Port: 443
          Type: HTTPS
          ResourcePath: /health
          FullyQualifiedDomainName: ${self:custom.domain}
          RequestInterval: 30
          FailureThreshold: 3
```

## Alerting

### 1. SNS Topics

Create notification topics:

```yaml
resources:
  Resources:
    AlertTopic:
      Type: AWS::SNS::Topic
      Properties:
        TopicName: ${self:service}-${sls:stage}-alerts
```

### 2. Alert Configuration

Configure alert actions:

```yaml
    ErrorAlarm:
      Type: AWS::CloudWatch::Alarm
      Properties:
        AlarmActions:
          - Ref: AlertTopic
        OKActions:
          - Ref: AlertTopic
```

## Dashboard

### 1. CloudWatch Dashboard

Create comprehensive dashboard:

```yaml
resources:
  Resources:
    MainDashboard:
      Type: AWS::CloudWatch::Dashboard
      Properties:
        DashboardName: ${self:service}-${sls:stage}-main
        DashboardBody:
          widgets:
            - type: metric
              properties:
                metrics:
                  - [ "AWS/Lambda", "Invocations" ]
                  - [ ".", "Errors" ]
                  - [ ".", "Duration" ]
                  - [ "AWS/RDS", "CPUUtilization" ]
                  - [ ".", "DatabaseConnections" ]
                period: 300
                region: ${self:provider.region}
```

## Next Steps

- [Performance Tuning](performance.md) - Optimize your application
- [Troubleshooting Guide](troubleshooting.md) - Debug common issues
- [Security Monitoring](security.md) - Security-specific monitoring 