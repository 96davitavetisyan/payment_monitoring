<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->can('view all projects')
            ? Project::all()
            : Project::forUser(auth()->id())->get();

        return response()->json($projects);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'start_date' => 'nullable|date',
            'responsible_user_id' => 'required|exists:users,id',
            'status' => 'required'
        ]);

        $project = Project::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Project created',
            'data' => $project
        ], 201);
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'start_date' => 'nullable|date',
            'responsible_user_id' => 'required|exists:users,id',
            'status' => 'required|in:active,suspended',
        ]);

        $project->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Project updated',
            'data' => $project
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted'
        ]);
    }

    // Suspend / Activate
    public function toggleStatus(Project $project)
    {
        $project->status = $project->status === 'active' ? 'suspended' : 'active';
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project status updated',
            'status' => $project->status
        ]);
    }
}
