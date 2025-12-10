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
