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
