<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products for a project.
     */
    public function index(Project $project)
    {
        // Проверка доступа к проекту
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $products = $project->products()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Проверка доступа к проекту
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        $product = $project->products()->create([
            ...$validated,
            'stock' => $validated['stock'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Проверка доступа к проекту
        if ($product->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($product);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Проверка доступа к проекту
        if ($product->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category' => 'sometimes|string|max:255',
            'image_url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Проверка доступа к проекту
        if ($product->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
