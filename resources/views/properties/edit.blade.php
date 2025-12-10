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
