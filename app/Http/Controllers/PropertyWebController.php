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
