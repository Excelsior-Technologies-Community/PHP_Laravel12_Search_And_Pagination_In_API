PHP_Laravel12_Search_And_Pagination_In_API
---

This project demonstrates a complete Property Listing System using Laravel 12, including:
---
 REST API for CRUD operations
 Search by ID, title, description, price, location
 Pagination (API + Web)
 Soft Delete (deleted_at + status=deleted)
 Bootstrap Web UI (Add/Edit/Delete/View)
 Clean Controller + Model with comments
 Fully documented steps & commands

 Features
---
API + Web UI (Blade)

Search & Pagination

Soft deletes supported (deleted_at)

Clean, easy code for beginners

Fully REST-style API responses

MySQL database support

Step-by-step artisan commands

 Requirements
---
PHP ≥ 8.1

Composer

MySQL

Node.js (optional, not needed for this tutorial)

 Installation Guide
---
Step 1 — Create Laravel 12 Project

Why: We start a fresh Laravel project to structure the app properly.
```
# 1) Create Laravel project
composer create-project laravel/laravel PHP_Laravel12_Search_And_Pagination_In_API "12.*"

# 2) Enter project directory
cd PHP_Laravel12_Search_And_Pagination_In_API

# 3) Generate app key for security
php artisan key:generate

# 4) Serve locally
php artisan serve

# Opens at: http://127.0.0.1:8000Step 1 — Configure .env (Database)
```
Why: Laravel needs database credentials to store properties.


Open .env and update:
```
APP_NAME="Laravel Search Pagination API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=search_pagination_api
DB_USERNAME=root
DB_PASSWORD=

```
Create database in MySQL:
```
CREATE DATABASE search_pagination_api;
```

Step 2 — Create Model + Migration

Why: Laravel uses Models to represent DB tables and Migrations to define table structure.

# Create model + migration + factory
```
php artisan make:model Property -m -f
```
Explanation:

 Property.php → model

 xxxx_create_properties_table.php → migration

 PropertyFactory.php → optional for generating fake data

Migration — database/migrations/xxxx_create_properties_table.php
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); // Auto increment ID
            $table->string('title'); // Property title
            $table->text('description')->nullable(); // Description optional
            $table->decimal('price', 12, 2)->default(0); // Price with 2 decimals
            $table->string('location')->nullable(); // Location optional
            $table->unsignedBigInteger('created_by')->nullable(); // Creator user ID
            $table->unsignedBigInteger('updated_by')->nullable(); // Last updated by
            $table->enum('status', ['active','inactive','deleted'])->default('active'); // Property status
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at (soft delete)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
```

Explanation:

softDeletes() allows soft deleting records.

status helps track active/inactive/deleted without physically removing the record.


Model — app/Models/Property.php
```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    // Mass assignable fields
    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'created_by',
        'updated_by',
        'status',
    ];

    // Cast price as decimal, status as boolean
    protected $casts = [
        'price' => 'decimal:2',
        
    ];
}
```

Explanation:

$fillable allows mass assignment safely.

$casts ensures correct data type handling.


Step 3 — Run Migrations
```

php artisan migrate
```

Explanation:
Why: This creates the properties table in the database.



Step 4 — Create Controllers
```
php artisan make:controller Api/PropertyController
php artisan make:controller PropertyWebController

```
Explanation:

Api/PropertyController → REST API endpoints

PropertyWebController → Web UI pages



API Controller — app/Http/Controllers/Api/PropertyController.php
```
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    // List all properties with search + pagination
    public function index(Request $request)
    {
        $query = Property::query();

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
            $q->where('id', $search) // exact match for ID
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('price', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
             });
        }

        // Pagination (3 per page)
        $properties = $query->orderBy('id', 'asc')->paginate(3);

        return response()->json($properties);
    }

    // Store a new property
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'location' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $property = Property::create($request->all());
        return response()->json($property, 201);
    }

    // Show single property
    public function show($id)
    {
        $property = Property::findOrFail($id);
        return response()->json($property);
    }

    // Update property
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'location' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $property->update($request->all());
        return response()->json($property);
    }

    // Delete property (soft delete)
   public function destroy($id)
{
    $property = Property::findOrFail($id);

    // update status column
    $property->status = 'deleted';
    $property->save();

    // soft delete (fills deleted_at automatically)
    $property->delete();

    return response()->json([
        'message' => 'Property deleted successfully',
        'status' => $property->status,
        'deleted_at' => $property->deleted_at
    ]);
}


}

```
Explanation:

.paginate() adds pagination and JSON meta automatically.

Soft delete keeps data but marks deleted_at.

Validation ensures correct input.


Web Controller — PropertyWebController.php
```
<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyWebController extends Controller
{
    /**
     * Display a listing of the properties with search and pagination.
     */
  public function index(Request $request)
{
    $query = Property::query();

    // Search by id, title, price, or location
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('id', $search) // exact match for ID
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('price', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    // Pagination (10 per page)
    $properties = $query->orderBy('id', 'asc')->paginate(10);

    return view('properties.index', compact('properties'));
}

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        return view('properties.create');
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'location' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id(); // Assign current user ID
        $data['updated_by'] = auth()->id(); // Also assign updated_by initially

        Property::create($data);

        return redirect()->route('properties.index')->with('success', 'Property added successfully.');
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit($id)
    {
        $property = Property::findOrFail($id);
        return view('properties.edit', compact('property'));
    }

    /**
     * Update the specified property in storage via POST.
     */
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'location' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id(); // Assign current user ID

        $property->update($data);

        return redirect()->route('properties.index')->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified property from storage via DELETE.
     */
    public function destroy($id)
    {
        $property = Property::findOrFail($id);
        $property->delete(); // Soft delete

        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');
    }
}
```
Explanation:

Handles Web pages: Index, Create, Edit

Soft delete integrated

Pagination for table


Step 5 — Routes

API Routes — routes/api.php
```
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes using only GET and POST methods.
|
*/

// Get all properties (with search + pagination)
Route::get('properties', [PropertyController::class, 'index']);

// Get single property by ID
Route::get('properties/{id}', [PropertyController::class, 'show']);

// Create a new property
Route::post('properties/create', [PropertyController::class, 'store']);

// Update property by ID (via POST)
Route::post('properties/update/{id}', [PropertyController::class, 'update']);

// Delete property by ID (via POST)
Route::post('properties/delete/{id}', [PropertyController::class, 'destroy']);

// Optional test route
Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});

```

Web Routes — routes/web.php
```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyWebController;


Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| GET → show pages (index, create, edit)  
| POST → store & update  
| DELETE → delete
|
*/

// Redirect home to properties list


// List all properties
Route::get('properties', [PropertyWebController::class, 'index'])->name('properties.index');

// Show create property form
Route::get('properties/create', [PropertyWebController::class, 'create'])->name('properties.create');

// Store new property
Route::post('properties/store', [PropertyWebController::class, 'store'])->name('properties.store');

// Show edit property form
Route::get('properties/edit/{id}', [PropertyWebController::class, 'edit'])->name('properties.edit');

// Update property
Route::post('properties/update/{id}', [PropertyWebController::class, 'update'])->name('properties.update');

// Delete property
Route::delete('properties/delete/{id}', [PropertyWebController::class, 'destroy'])->name('properties.destroy');

```

Explanation:

API routes: return JSON

Web routes: return Blade views

Soft delete via POST/DELETE


 Step 6 — Views (Blade Templates)

Create folder: resources/views/properties/
```
index.blade.php → List properties with search + pagination
create.blade.php → Add property form
edit.blade.php → Edit property form
```
resources/views/properties/index.blade.php
```
<!-- resources/views/properties/index.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Properties List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Properties List</h2>

    <!-- Search Form -->
    <form method="GET" action="{{ route('properties.index') }}" class="mb-3 d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search title or location" value="{{ request('search') }}">
        <button class="btn btn-primary">Search</button>
    </form>

    <!-- Add Property Button -->
    <a href="{{ route('properties.create') }}" class="btn btn-success mb-2">Add Property</a>

    <!-- Properties Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Price</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($properties as $property)
            <tr>
                <td>{{ $property->id }}</td>
                <td>{{ $property->title }}</td>
                <td>{{ $property->description }}</td>
                <td>{{ $property->price }}</td>
                <td>{{ $property->location }}</td>
                <td>{{ $property->status ? 'Active' : 'Inactive' }}</td>
                <td>
                    <!-- Edit Button -->
                    <a href="{{ route('properties.edit', $property->id) }}" class="btn btn-primary btn-sm">Edit</a>

                    <!-- Delete Form -->
                    <form action="{{ route('properties.destroy', $property->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this property?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $properties->withQueryString()->links() }}
</div>
</body>
</html>
```


resources/views/properties/create.blade.php
```
<!-- resources/views/properties/create.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Add Property</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Add Property</h2>

    <form action="{{ route('properties.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <button class="btn btn-success">Save Property</button>
        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
```


resources/views/properties/edit.blade.php
```
<!-- resources/views/properties/edit.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Property</h2>

    <form action="{{ route('properties.update', $property->id) }}" method="POST">
        @csrf
        <!-- Update via POST -->
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ $property->title }}" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $property->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="{{ $property->price }}" required>
        </div>

        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" value="{{ $property->location }}">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" {{ $property->status ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$property->status ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button class="btn btn-primary">Update Property</button>
        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
```


Step 7 — API Testing (Postman)
---
1️) Get all properties
```
GET /api/properties
```
2️) Search
```
GET /api/properties?search=Gandhinagar
```

3️) Create
```
POST /api/properties/create

```
JSON:
```
json
  {
     "title": "Shivpuran",
     "description": "This is Book.",
     "price": 299.00,
     "location": "Gandhinagar",
     "status": true
  }
```
4️) Update
```
POST /api/properties/update/5
```

5️) Delete (Soft Delete)
```
POST /api/properties/delete/5
```

Step 8 — Folder Tree
```
PHP_Laravel12_Search_And_Pagination_In_API/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Api/PropertyController.php
│   │       └── PropertyWebController.php
│   └── Models/Property.php
├── database/
│   ├── factories/PropertyFactory.php
│   └── migrations/create_properties_table.php
├── resources/
│   └── views/properties/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
├── routes/
│   ├── api.php
│   └── web.php
└── .env
