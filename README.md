# B2B Travel Platform

## 🎯 Requirements : 
### Premise: 
- Build V1 of a B2B Travel platform
### Objectives: 
- Archetecht a system that handles multiple 3rd party data platforms.  
- The 3rd party data will be in different shapes, and formats.
- Build examples for 2 different formats
### 🎯 Tech Requirements : 
- Laravel

## 🎯 Implementation Overview

This is a Mock  implementation :

- **Multiple Data Formats**: JSON (Fergusontravel) and XML (StephensTravel) vendor integration  
- **Principles**: Interface-based design with dependency injection
 Clear separation of concerns with Data Transformation Object(DTO), and services
- **Unified API**: Single endpoint exposing aggregated multi-vendor data

## 🏗️ Decisions. 
- I wanted a Single Travel Search service that all requests would go through.  Flights and hotels take the argument type.  
- I wanted a unified request and return Typing. travelSearchResponse && TravelSearchRequest. to create a contract. 
- VendorService Provider. - I wanted a single place for all vendors to be registered.
- I used singletons to not create multiple instances of a Class.
- There needed to be a DataTransformerInterface that created a contract between all the vendors.
- I used 1 typing for SearchCriteria for simplicity.  
- TravelSearchResource - I wanted the reponse to be unified.  Not a generic.
- merge the results from different vendors in Travel Search Service





- 
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

### Multi-Format Data Handling
- **JSON Format**: FergusontravelVendor with mock GraphQL-style responses
- **XML Format**: StephensTravelVendor with SimpleXML processing
- **Unified Output**: Both formats transformed to consistent array structure
- **Strategy Pattern**: DataManager orchestrates format-specific transformers



The endpoints will return mock data processed through both JSON and XML transformers, demonstrating the type-safe multi-format aggregation.

---
