<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers for a project.
     */
    public function index(Project $project)
    {
        // Проверка доступа к проекту
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $customers = $project->customers()
            ->withCount('orders')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request, Project $project)
    {
        // Проверка доступа к проекту
        if ($project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'telegram_id' => 'required|integer|unique:customers,telegram_id',
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $customer = $project->customers()->create($validated);

        return response()->json($customer, 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        // Проверка доступа к проекту
        if ($customer->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $customer->loadCount('orders');
        $customer->load('orders');

        return response()->json($customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        // Проверка доступа к проекту
        if ($customer->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        // Проверка доступа к проекту
        if ($customer->project->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
