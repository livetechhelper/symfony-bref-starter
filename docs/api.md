# API Documentation

This project incorporates [API Platform](https://api-platform.com/) to provide a feature-rich RESTful API with JWT authentication.

## Overview

API Platform offers a comprehensive solution for building APIs:

- **RESTful by Design**: Follows REST principles for predictable and standardized endpoints
- **JSON-LD & Hydra Support**: Advanced hypermedia capabilities
- **Swagger/OpenAPI Documentation**: Interactive documentation
- **JWT Authentication**: Secure endpoints with JSON Web Tokens

## Authentication

The API uses JWT (JSON Web Tokens) for authentication:

### 1. JWT Configuration

The project uses [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) with API Platform:

```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600 # 1 hour
```

#### Generating JWT Keys

You need to generate the JWT keys before using the API:

```bash
# Generate the JWT keys
docker compose exec -w /var/task dev_php php bin/console lexik:jwt:generate-keypair

# Verify the keys were created
docker compose exec -w /var/task dev_php ls -la config/jwt/
```

This will create the required keys in the `config/jwt/` directory.

### 2. Security Configuration

```yaml
# config/packages/security.yaml
security:
    # ...
    firewalls:
        # ...
        api:
            pattern: ^/api
            stateless: true
            provider: app_user_provider
            jwt: ~
            json_login:
                check_path: /api/login
                username_path: email
                password_path: password
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
```

### 3. Obtaining a JWT Token

To get a token, send a POST request to `/api/login`:

```bash
curl -X POST https://your-domain.com/api/login -d '{"email":"user@example.com","password":"password"}'
```

The response will include a JWT token:

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### 4. Using the Token

Include the token in your API requests:

```bash
curl -X GET https://your-domain.com/api/users -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

## API Documentation Interface

The API documentation is available at `/api/docs`:

- **Interactive Documentation**: Test endpoints directly from the browser
- **Authentication Form**: Log in via the documentation interface
  - Click the "Authorize" button
  - Enter your credentials to get a JWT token
  - The token will be automatically used for subsequent requests

## Available Resources

The following resources are available through the API:

| Resource | Endpoint | Methods | Description |
|----------|----------|---------|-------------|
| Users | `/api/users` | GET, POST | User management |
| User | `/api/users/{id}` | GET, PUT, DELETE | Individual user operations |

## Entity Configuration

API resources are configured using PHP attributes:

```php
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class User
{
    // ...
}
```

## Custom Operations

You can create custom operations beyond the standard CRUD actions:

```php
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete(),
        new GetCollection(
            name: 'get_user_stats',
            uriTemplate: '/users/stats',
            controller: UserStatsController::class
        )
    ]
)]
class User
{
    // ...
}
```

## Validation

Request data is validated using Symfony's validator:

```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;
    
    // ...
}
```

## Further Documentation

For more information, refer to:

- [API Platform Documentation](https://api-platform.com/docs/)
- [LexikJWTAuthenticationBundle Documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md)
- [JSON Web Token Documentation](https://jwt.io/) 