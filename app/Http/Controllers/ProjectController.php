<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if user can view all projects or only their own
        $projects = auth()->user()->can('view_all_projects')
            ? Project::with('responsibleUser')->get()
            : Project::forUser(auth()->id())->with('responsibleUser')->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Project created',
            'data' => $project->load('responsibleUser')
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        // Check if user can view this project
        if (!auth()->user()->can('view_all_projects') && $project->responsible_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $project->load('responsibleUser')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Project updated',
            'data' => $project->fresh()->load('responsibleUser')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        // Check permission
        if (!auth()->user()->can('delete_projects')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted'
        ]);
    }

    /**
     * Toggle project status (activate/suspend)
     *
     * @param  Project  $project
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Project $project)
    {
        // Check if user can suspend/activate projects
        if (!auth()->user()->can('suspend_projects') && !auth()->user()->can('activate_projects')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->status = $project->status === 'active' ? 'suspended' : 'active';
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Project status updated',
            'status' => $project->status
        ]);
    }
}
