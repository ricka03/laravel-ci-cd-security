<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * REFERENCE SOLUTION (instructor): fixes R01, R04, R05.
 */
class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project->id)->with('status', 'Project created.');
    }

    /** R01 fix: load, then authorize with the Policy (organization ownership check). */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        // THis is the code/query that needs to be fixed

        $project = Project::with(['documents', 'organization'])->
        where('organization_id', $user->organization_id)->findOrFail($id);

        $this->authorize('view', $project);

        return view('projects.show', compact('project'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, $id): RedirectResponse
    {
        $project = Project::findOrFail($id);
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project->id)->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }

    /** R04 fix: query builder with bound parameters. R05 fix: generic error, details only in logs. */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');

        try {
            $results = Project::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('name', 'like', '%'.$q.'%')
                ->get(['id', 'name', 'description']);
        } catch (\Throwable $e) {
            Log::error('Project search failed', ['exception' => $e]);

            return response('Search is temporarily unavailable.', 500);
        }

        return view('projects.search', ['results' => $results, 'q' => $q]);
    }
}
