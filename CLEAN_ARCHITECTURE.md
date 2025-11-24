# Clean Architecture Implementation Guide

This document outlines the clean architecture structure implemented in LAPKEU Warmindo application.

## Overview

The application follows **Clean Architecture** principles with clear separation of concerns across multiple layers:

```
┌─────────────────────────────────────────────────┐
│           Controllers (HTTP Layer)              │
└─────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────┐
│          Services (Business Logic)              │
└─────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────┐
│        Repositories (Data Access Layer)         │
└─────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────┐
│           Models (Database Layer)               │
└─────────────────────────────────────────────────┘
```

## Directory Structure

```
app/
├── Controllers/              # HTTP request handlers
│   ├── BaseController.php
│   └── [YourController].php
├── Services/                 # Business logic layer
│   ├── BaseService.php
│   ├── UserService.php
│   └── [YourService].php
├── Repositories/             # Data access abstraction
│   ├── RepositoryInterface.php
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   └── [YourRepository].php
├── Models/                   # Database models
│   └── [YourModel].php
├── DTOs/                     # Data Transfer Objects
│   ├── BaseDTO.php
│   └── [YourDTO].php
├── Requests/                 # Form validation requests
│   ├── FormRequest.php
│   └── [YourFormRequest].php
├── Responses/                # Response builders
│   └── ApiResponse.php
├── Exceptions/               # Custom exceptions
│   ├── AppException.php
│   ├── ValidationException.php
│   ├── ResourceNotFoundException.php
│   └── [YourException].php
└── Domain/                   # Domain models (optional)
    └── [YourDomainModel].php
```

## Layers Explained

### 1. **Controller Layer** (HTTP)
**Location**: `app/Controllers/`

Handles HTTP requests and responses. Controllers should be thin and delegate business logic to services.

**Responsibilities**:
- Validate HTTP requests
- Call appropriate services
- Return responses
- Handle redirects and error responses

**Example**:
```php
<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Requests\StoreUserRequest;

class UserController extends BaseController
{
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function store()
    {
        $request = new StoreUserRequest();
        $validated = $request->validated();

        $userId = $this->userService->register($validated);

        return redirect()->to('users')->with('message', 'User created successfully');
    }
}
```

### 2. **Service Layer** (Business Logic)
**Location**: `app/Services/`

Contains all business logic. Services orchestrate repositories and enforce business rules.

**Responsibilities**:
- Execute business logic
- Validate business rules
- Orchestrate repository calls
- Handle transactions
- Throw exceptions for error handling

**Example**:
```php
<?php
namespace App\Services;

class UserService extends BaseService
{
    public function register(array $data): int
    {
        // Check business rules
        if ($this->repository->findByEmail($data['email'])) {
            throw new ValidationException('Email already registered');
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Create user
        return $this->create($data);
    }
}
```

### 3. **Repository Layer** (Data Access)
**Location**: `app/Repositories/`

Abstracts database access. Repositories provide a clean interface for data operations without exposing database details.

**Responsibilities**:
- Perform database queries
- Return data entities
- Implement repository pattern
- Provide reusable data access methods

**Example**:
```php
<?php
namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new UserModel());
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }
}
```

### 4. **Model Layer** (Database)
**Location**: `app/Models/`

CodeIgniter 4 Models that define database table structure and relationships.

**Responsibilities**:
- Define table schema
- Cast data types
- Define relationships
- Validation rules

### 5. **DTO Layer** (Data Transfer)
**Location**: `app/DTOs/`

Data Transfer Objects for request/response handling, decoupling API contracts from internal representation.

**Example**:
```php
<?php
namespace App\DTOs;

class UserDTO extends BaseDTO
{
    public int $id;
    public string $name;
    public string $email;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### 6. **Request Layer** (Validation)
**Location**: `app/Requests/`

Form requests handle validation before data reaches services.

**Example**:
```php
<?php
namespace App\Requests;

class StoreUserRequest extends FormRequest
{
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|is_unique[user.email]',
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'email' => [
            'is_unique' => 'Email already registered'
        ]
    ];
}
```

### 7. **Exception Layer** (Error Handling)
**Location**: `app/Exceptions/`

Custom exceptions for error handling with proper HTTP status codes.

**Types**:
- `AppException` - Base exception
- `ValidationException` - Validation failures
- `ResourceNotFoundException` - Resource not found
- Custom domain exceptions

**Example**:
```php
throw new ValidationException('Validation failed', $errors);
throw new ResourceNotFoundException('User', 5);
```

## Design Patterns Used

### 1. **Repository Pattern**
Abstracts data access, allowing easy switching between data sources.

```
Service → Repository Interface → Concrete Repository → Model → Database
```

### 2. **Dependency Injection**
Services receive repositories through constructors, promoting loose coupling.

```php
public function __construct(UserRepository $repository)
{
    parent::__construct($repository);
}
```

### 3. **DTO Pattern**
Transfer data between layers without exposing internal structure.

### 4. **Template Method Pattern**
`BaseService` and `BaseRepository` provide template methods for common operations.

## Flow Example: Creating a User

### Step 1: HTTP Request
```
POST /users
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret"
}
```

### Step 2: Controller
```php
public function store()
{
    // 1. Validate request
    $request = new StoreUserRequest();
    $validated = $request->validated();

    // 2. Call service
    $userId = $this->userService->register($validated);

    // 3. Return response
    return redirect()->to("users/{$userId}")->with('message', 'User created');
}
```

### Step 3: Service
```php
public function register(array $data): int
{
    // 1. Check business rules
    if ($this->repository->findByEmail($data['email'])) {
        throw new ValidationException('Email already registered');
    }

    // 2. Hash password
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

    // 3. Delegate to repository
    return $this->create($data);
}
```

### Step 4: Repository
```php
public function create(array $data): int|string
{
    return $this->model->insert($data);
}
```

### Step 5: Model
```php
// UserModel handles database insertion
// Returns insert ID
```

## Best Practices

### 1. **Keep Controllers Thin**
Controllers should only handle HTTP concerns. Move logic to services.

❌ **Wrong**:
```php
$user = new User();
$user->name = $this->request->getPost('name');
$user->email = $this->request->getPost('email');
$user->save();
```

✅ **Correct**:
```php
$request = new StoreUserRequest();
$userId = $this->userService->register($request->validated());
```

### 2. **Services Handle Business Logic**
Services should contain all business rules and validations.

### 3. **Repositories for Data Access**
Never access models directly in controllers. Use repositories.

❌ **Wrong**:
```php
$user = User::find(1);
```

✅ **Correct**:
```php
$user = $this->userRepository->find(1);
```

### 4. **Use DTOs for API Responses**
Transfer data using DTOs instead of raw arrays/models.

### 5. **Throw Meaningful Exceptions**
Use custom exceptions to communicate errors clearly.

```php
throw new ResourceNotFoundException('User', 5);
throw new ValidationException('Invalid data', $errors);
```

### 6. **Use Type Hints**
Always specify parameter and return types.

```php
public function register(array $data): int
{
    // ...
}
```

### 7. **Single Responsibility Principle**
Each class has one reason to change.

## Creating New Features

### 1. Create Model
```bash
php spark make:model YourModel
```

### 2. Create Repository
```php
class YourRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new YourModel());
    }
}
```

### 3. Create Service
```php
class YourService extends BaseService
{
    public function __construct()
    {
        $this->repository = new YourRepository();
        parent::__construct($this->repository);
    }
}
```

### 4. Create Form Request (if needed)
```php
class StoreYourRequest extends FormRequest
{
    protected $rules = [
        'name' => 'required|string',
    ];
}
```

### 5. Create Controller
```php
class YourController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new YourService();
    }
}
```

## Testing

With this architecture, testing becomes easier:

### Test Service Logic
```php
public function testUserRegistration()
{
    $service = new UserService();
    $userId = $service->register([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secret'
    ]);

    $this->assertIsInt($userId);
}
```

### Mock Repository
```php
$repositoryMock = $this->createMock(UserRepository::class);
$service = new UserService($repositoryMock);
```

## Benefits

1. **Maintainability** - Code is organized and easy to find
2. **Testability** - Each layer can be tested independently
3. **Scalability** - Easy to add new features without breaking existing code
4. **Flexibility** - Easy to swap implementations (e.g., different repositories)
5. **Reusability** - Services and repositories can be reused across controllers
6. **Type Safety** - Strong typing prevents errors
7. **Error Handling** - Centralized exception handling

## Common Mistakes to Avoid

1. ❌ Putting business logic in controllers
2. ❌ Accessing models directly from controllers
3. ❌ Not using form requests for validation
4. ❌ Returning models directly from services
5. ❌ Mixing concerns in a single class
6. ❌ Not using dependency injection
7. ❌ Ignoring error handling

## References

- [Clean Code by Robert C. Martin](https://www.oreilly.com/library/view/clean-code-a/9780136083238/)
- [Clean Architecture by Robert C. Martin](https://www.oreilly.com/library/view/clean-architecture-a/9780134494326/)
- [Design Patterns: Elements of Reusable Object-Oriented Software](https://en.wikipedia.org/wiki/Design_Patterns)
- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [Dependency Injection](https://martinfowler.com/articles/injection.html)
