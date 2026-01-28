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
        // ✅ ДОБАВИТЬ template_type в validation!
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'template_type' => 'required|string', // ✅ НОВОЕ!
            'bot_token' => 'nullable|string',
        ]);

        // ✅ Динамическая тема на основе category
        $themePresets = [
            'electronics' => ['bg' => '#F5F7FA', 'cardBg' => '#FFFFFF', 'btn' => '#2563EB', 'text' => '#1E293B'],
            'fashion' => ['bg' => '#FFF5F7', 'cardBg' => '#FFFFFF', 'btn' => '#EC4899', 'text' => '#831843'],
            'food' => ['bg' => '#FFF7ED', 'cardBg' => '#FFFFFF', 'btn' => '#F97316', 'text' => '#7C2D12'],
            'services' => ['bg' => '#F0FDF4', 'cardBg' => '#FFFFFF', 'btn' => '#10B981', 'text' => '#064E3B'],
            'education' => ['bg' => '#EEF2FF', 'cardBg' => '#FFFFFF', 'btn' => '#6366F1', 'text' => '#312E81'],
            'health' => ['bg' => '#F0FDFA', 'cardBg' => '#FFFFFF', 'btn' => '#14B8A6', 'text' => '#134E4A'],
        ];

        $categoryTheme = $themePresets[$validated['category']] ?? $themePresets['electronics'];

        $project = $request->user()->projects()->create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'template_type' => $validated['template_type'],
            'bot_token' => $validated['bot_token'] ?? null,
            'status' => 'draft',
            'is_paid' => false,
            'theme_config' => [
                ...$categoryTheme,
                'shadow' => 50,
                'radius' => 16,
                'font' => 'modern',
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
            'template_type' => 'sometimes|string', 
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
