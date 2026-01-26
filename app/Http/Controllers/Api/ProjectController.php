<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'bot_token' => 'nullable|string',
        ]);

        $project = $request->user()->projects()->create([
            ...$validated,
            'status' => 'draft',
            'is_paid' => false,
            'theme_config' => [
                'bg' => '#FFFFFF',
                'cardBg' => '#FFFFFF',
                'btn' => '#000000',
                'text' => '#1A1A1A',
                'shadow' => 50,
                'radius' => 16,
                'font' => 'modern',
                'productCategory' => 'electronics'
            ]
        ]);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        // $this->authorize('view', $project);
        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        // $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'bot_token' => 'nullable|string',
            'theme_config' => 'sometimes|array',
        ]);

        $project->update($validated);
        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        // $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted']);
    }

    public function updateTheme(Request $request, Project $project)
    {
    $validated = $request->validate([
        'theme_config' => 'required|array',
        'theme_config.preset' => 'required|string|in:modern,minimal,vibrant,elegant,dark,warm,eco,cyberpunk'
    ]);

    // Update ONLY theme_config, don't touch status
    $project->update([
        'theme_config' => $validated['theme_config']
    ]);

    return response()->json($project);
    }


    public function activate(Request $request, Project $project)
    {
        // $this->authorize('update', $project);

        // TODO: Добавить проверку платежа

        $project->update([
            'status' => 'active',
            'is_paid' => true
        ]);

        return response()->json($project);
    }
}