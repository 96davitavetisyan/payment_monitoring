<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Feedback;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks for a project.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        // Check permission
        if (!auth()->user()->can('view_feedback')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedbacks = $project->feedbacks()->with('accountManager')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * Store a newly created feedback in storage.
     *
     * @param  \App\Http\Requests\StoreFeedbackRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFeedbackRequest $request, Project $project)
    {
        $data = $request->validated();
        $data['account_manager_id'] = auth()->id();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('feedbacks');
        }

        $feedback = $project->feedbacks()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Feedback created',
            'data' => $feedback->load('accountManager')
        ], 201);
    }

    /**
     * Update the specified feedback in storage.
     *
     * @param  \App\Http\Requests\UpdateFeedbackRequest  $request
     * @param  Project  $project
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFeedbackRequest $request, Project $project, Feedback $feedback)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('feedbacks');
        }

        $feedback->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Feedback updated',
            'data' => $feedback->fresh()->load('accountManager')
        ]);
    }

    /**
     * Remove the specified feedback from storage.
     *
     * @param  Project  $project
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, Feedback $feedback)
    {
        // Check permission - only the creator or admin can delete
        if (!auth()->user()->can('manage_feedback') && $feedback->account_manager_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted'
        ]);
    }
}
