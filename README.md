# B2B Travel Platform

## 🎯 Implementation Overview

This is a Mock  implementation :

- **Multiple Data Formats**: JSON (Fergusontravel) and XML (StephensTravel) vendor integration  
- **SOLID Principles**: Interface-based design with dependency injection
- **Clean Architecture**: Clear separation of concerns with DTOs, transformers, and services
- **Unified API**: Single endpoint exposing aggregated multi-vendor data

## 🏗️ Data Flow

```
Request → TravelController → TravelSearchService → Multiple Vendors (JSON/XML) → DataTransformers → Unified Response
```

## 🚀 API Endpoints

#### Hotel Search
```http
GET /api/v1/travel/hotels/search?destination=Miami&check_in_date=2024-02-01&check_out_date=2024-02-05
```

#### Flight Search  
```http
GET /api/v1/travel/flights/search?origin=JFK&destination=LAX&departure_date=2024-02-01&return_date=2024-02-08
```


## 📁 Core Architecture

```
app/
├── DTOs/Vendor/                    # Type-safe Data Transfer Objects
│   ├── SearchCriteria.php         # Typed search parameters  
│   └── VendorResponse.php          # Standardized vendor responses
│
├── Services/                       # Business Logic Layer
│   ├── TravelSearchService.php    # Multi-vendor orchestration
│   ├── DataManagement/
│   │   ├── DataManager.php        # Transformer registry
│   │   ├── Contracts/DataTransformerInterface.php
│   │   └── Transformers/          # Format-specific transformers
│   │       ├── FergusontravelTransformer.php  # JSON handler
│   │       └── StephensTravelTransformer.php  # XML handler
│   │
│   └── Vendors/V1/                 # Vendor implementations
│       ├── FergusontravelVendor.php    # JSON/GraphQL vendor
│       └── StephensTravelVendor.php    # XML vendor
│
└── Http/Controllers/Api/V1/
    └── TravelController.php       # Unified API endpoints
```

## ✨ Key Implementation Details

### Type Safety Demonstration
- **Strict Types**: All files use `declare(strict_types=1)`
- **Typed Properties**: DTOs use readonly typed properties (PHP 8.1+)
- **Method Signatures**: Type hints for all parameters and return types
- **Interface Contracts**: Enforced vendor implementations

### Multi-Format Data Handling
- **JSON Format**: FergusontravelVendor with mock GraphQL-style responses
- **XML Format**: StephensTravelVendor with SimpleXML processing
- **Unified Output**: Both formats transformed to consistent array structure
- **Strategy Pattern**: DataManager orchestrates format-specific transformers


## 🚀 Quick Test

The implementation uses mock data for demonstration. Simply install and test:

```bash
# Install dependencies
composer install

# Test the API endpoints
GET /api/v1/travel/hotels/search?destination=Miami&check_in_date=2024-02-01&check_out_date=2024-02-05
GET /api/v1/travel/flights/search?origin=JFK&destination=LAX&departure_date=2024-02-01&return_date=2024-02-08
GET /api/v1/travel/health
```

The endpoints will return mock data processed through both JSON and XML transformers, demonstrating the type-safe multi-format aggregation.

---
