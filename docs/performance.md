# Performance Tuning Guide

This guide covers performance optimization techniques for your Symfony Bref application on AWS Lambda.

## Lambda Configuration

### 1. Memory and CPU

Optimize Lambda memory allocation:

```yaml
# serverless.yml
provider:
  memorySize: 1024  # Adjust based on application needs
  timeout: 29       # Max 29 seconds for API Gateway

functions:
  app:
    memorySize: 1024
    timeout: 29
```

### 2. Provisioned Concurrency

Enable warm instances:

```yaml
functions:
  app:
    provisionedConcurrency: 5  # Adjust based on traffic patterns
```

## Cold Start Optimization

### 1. Code Organization

Optimize dependency loading:

```php
// config/bundles.php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    // Load only required bundles
];
```

### 2. Autoloader Optimization

Optimize Composer's autoloader:

```json
{
    "config": {
        "optimize-autoloader": true,
        "apcu-autoloader": true,
        "classmap-authoritative": true
    }
}
```

## Database Performance

### 1. Connection Management

Configure database pool:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        connections:
            default:
                driver: 'pdo_mysql'
                server_version: '8.0'
                charset: utf8mb4
                default_table_options:
                    charset: utf8mb4
                    collate: utf8mb4_unicode_ci
                options:
                    1002: "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
```

### 2. Query Optimization

Use Doctrine's result cache:

```php
$query = $em->createQuery('SELECT u FROM App\Entity\User u')
    ->setResultCacheDriver(new \Symfony\Component\Cache\DoctrineProvider(
        new \Symfony\Component\Cache\Adapter\ApcuAdapter()
    ))
    ->setResultCacheLifetime(3600);
```

## Caching Strategy

### 1. APCu Configuration

Configure APCu for system cache:

```yaml
# config/packages/cache.yaml
framework:
    cache:
        app: cache.adapter.apcu
        system: cache.adapter.system
        default_apcu_expiration: 3600
```

### 2. Redis for Session Storage

Use Redis for distributed caching:

```yaml
# config/packages/framework.yaml
framework:
    session:
        handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler
        save_path: "redis://${REDIS_HOST}:6379"
```

## Asset Optimization

### 1. Webpack Encore Configuration

Optimize asset building:

```javascript
// webpack.config.js
Encore
    .enableVersioning()
    .enableSourceMaps(!Encore.isProduction())
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .configureSplitChunks((splitChunks) => {
        splitChunks.chunks = 'all';
        splitChunks.minSize = 0;
    });
```

### 2. CloudFront Configuration

Configure CDN caching:

```yaml
resources:
  Resources:
    CloudFrontDistribution:
      Type: AWS::CloudFront::Distribution
      Properties:
        DistributionConfig:
          DefaultCacheBehavior:
            MinTTL: 0
            DefaultTTL: 3600
            MaxTTL: 86400
            Compress: true
```

## Code Optimization

### 1. Service Container

Use compiled container:

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
```

### 2. Route Loading

Optimize route loading:

```yaml
# config/routes.yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
```

## Monitoring Performance

### 1. X-Ray Tracing

Enable detailed tracing:

```yaml
# config/packages/aws.yaml
aws:
    xray:
        enabled: true
        sampling_rule:
            version: 1
            rules:
                - service_name: "*"
                  http_method: "*"
                  url_path: "*"
                  fixed_target: 1
                  rate: 0.05
```

### 2. Performance Metrics

Monitor key metrics:

```php
use Aws\CloudWatch\CloudWatchClient;

$cloudWatch->putMetricData([
    'Namespace' => 'YourApp/Performance',
    'MetricData' => [
        [
            'MetricName' => 'ResponseTime',
            'Value' => $duration,
            'Unit' => 'Milliseconds',
            'Dimensions' => [
                ['Name' => 'Environment', 'Value' => $_ENV['APP_ENV']]
            ]
        ]
    ]
]);
```

## HTTP Optimization

### 1. Keep-Alive Configuration

Enable HTTP keep-alive:

```yaml
# serverless.yml
provider:
  httpApi:
    payload: '2.0'
    keepAlive: true
```

### 2. Response Compression

Configure response compression:

```yaml
# config/packages/framework.yaml
framework:
    http_client:
        max_host_connections: 6
        default_options:
            headers:
                'Accept-Encoding': 'gzip'
```

## Best Practices

### 1. Dependency Management

Regular dependency updates:

```shell
# Update dependencies
composer update --prefer-dist --optimize-autoloader
```

### 2. Code Analysis

Use static analysis tools:

```shell
# Install PHPStan
composer require --dev phpstan/phpstan

# Run analysis
./vendor/bin/phpstan analyse src tests
```

## Load Testing

### 1. Artillery Configuration

Create load test scenarios:

```yaml
# load-test.yml
config:
  target: "https://your-api.execute-api.region.amazonaws.com"
  phases:
    - duration: 60
      arrivalRate: 5
      rampTo: 50
```

### 2. Performance Benchmarks

Set performance targets:

- Response time: < 200ms (p95)
- Error rate: < 0.1%
- Cold start: < 1s

## Next Steps

- [Monitoring Guide](monitoring.md) - Monitor performance metrics
- [Troubleshooting Guide](troubleshooting.md) - Debug performance issues
- [Security Guide](security.md) - Security impact on performance 