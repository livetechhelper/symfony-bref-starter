# Security Configuration Guide

This guide covers security best practices for your Symfony Bref application.

## Authentication Setup

### 1. User Entity Configuration

The `User` entity implements `UserInterface` and includes essential security fields:

```php
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?string $email = null;
    private array $roles = [];
    private ?string $password = null;
    // ... other properties
}
```

### 2. Security Configuration

Configure `security.yaml`:

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true
            logout:
                path: app_logout
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 week

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/register, roles: PUBLIC_ACCESS }
        - { path: ^/dashboard, roles: ROLE_USER }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

## Password Management

### 1. Password Validation

Use the `PasswordHasherFactory` for secure password handling:

```php
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Hash password before saving
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }
}
```

### 2. Password Reset Flow

1. Generate Reset Token:
   ```php
   use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

   $resetToken = $tokenGenerator->generateToken();
   $user->setResetToken($resetToken);
   ```

2. Send Reset Email:
   ```php
   $email = (new TemplatedEmail())
       ->to($user->getEmail())
       ->subject('Password Reset Request')
       ->htmlTemplate('reset_password/email.html.twig')
       ->context(['resetToken' => $resetToken]);
   ```

## CSRF Protection

### 1. Form Protection

Add CSRF protection to forms:

```php
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

$builder->add('_token', HiddenType::class, [
    'mapped' => false,
    'constraints' => new Csrf(['message' => 'Invalid CSRF token']),
]);
```

### 2. API Protection

For API endpoints:

```yaml
# config/packages/framework.yaml
framework:
    csrf_protection:
        enabled: true
```

## Session Security

### 1. Session Configuration

```yaml
# config/packages/framework.yaml
framework:
    session:
        handler_id: null
        cookie_secure: true
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
```

### 2. Session Management

```php
// Force session regeneration on security events
$session->migrate(true);
```

## AWS Security

### 1. IAM Roles

Minimum required permissions for Lambda:

```yaml
provider:
  iam:
    role:
      statements:
        - Effect: Allow
          Action:
            - s3:GetObject
            - s3:PutObject
          Resource: "arn:aws:s3:::${self:custom.bucket}/*"
        - Effect: Allow
          Action:
            - sqs:SendMessage
            - sqs:ReceiveMessage
          Resource: ${self:custom.queueArn}
```

### 2. VPC Security Groups

Restrict access to RDS:

```yaml
resources:
  Resources:
    DatabaseSecurityGroup:
      Type: AWS::EC2::SecurityGroup
      Properties:
        GroupDescription: RDS security group
        SecurityGroupIngress:
          - IpProtocol: tcp
            FromPort: 3306
            ToPort: 3306
            SourceSecurityGroupId: !Ref LambdaSecurityGroup
```

## Environment Variables

### 1. Sensitive Data

Never commit sensitive data. Use AWS Secrets Manager:

```yaml
provider:
  environment:
    APP_SECRET: ${ssm:/app/prod/secret}
    DATABASE_URL: ${ssm:/app/prod/db_url}
```

### 2. Production Settings

```dotenv
APP_ENV=prod
APP_DEBUG=0
```

## Security Headers

### 1. Configure Security Headers

```yaml
# config/packages/security_headers.yaml
security_headers:
    x_frame_options: DENY
    x_content_type_options: nosniff
    x_xss_protection: '1; mode=block'
    referrer_policy: strict-origin-when-cross-origin
    content_security_policy:
        default-src: "'self'"
        script-src: "'self' 'unsafe-inline'"
        style-src: "'self' 'unsafe-inline'"
```

## Monitoring and Logging

### 1. Error Logging

Configure Monolog for AWS CloudWatch:

```yaml
monolog:
    handlers:
        cloudwatch:
            type: service
            id: aws_logs.handler
```

### 2. Security Events

Log security events:

```php
$this->logger->alert('Failed login attempt', [
    'email' => $email,
    'ip' => $request->getClientIp(),
]);
```

## Next Steps

- [Deployment Guide](deployment.md) - Deploy your secure application
- [Monitoring Guide](monitoring.md) - Monitor security events
- [Performance Guide](performance.md) - Security impact on performance 