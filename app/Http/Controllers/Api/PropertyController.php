<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    // List all properties with search + pagination + filters
    public function index(Request $request)
    {
        $query = Property::query();

        // Search by ID, title, description, price, or location
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('price', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by status (active/inactive)
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
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

        // Update status column
        $property->status = 'deleted';
        $property->save();

        // Soft delete (fills deleted_at automatically)
        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully',
            'status' => $property->status,
            'deleted_at' => $property->deleted_at
        ]);
    }

    // Bulk soft delete properties
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:properties,id',
        ]);

        $properties = Property::whereIn('id', $request->ids)->get();

        foreach ($properties as $property) {
            $property->status = 'deleted';
            $property->save();
            $property->delete(); // soft delete
        }

        return response()->json([
            'message' => count($properties) . ' properties deleted successfully',
        ]);
    }
}