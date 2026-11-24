<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TRAINING LAB: this controller intentionally mixes correct and flawed code.
 * Participants must review each action and decide: implemented / missing / uncertain.
 */
class ProjectController extends Controller
{
    /** Tenant filtering applied in the query. */
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

    /** Project lookup by raw ID. */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $project = Project::with(['documents', 'organization'])-> where('organization_id', $user->organization_id)->findOrFail($id);

        Gate::authorize('view',$project);
        return view('projects.show', compact('project'));
    }

    public function edit(Request $request, $id)
    {
        $user = $request->user();
        $project = Project::where('organization_id', $user->organization_id)->findOrFail($id);
        Gate::authorize('view', $project);

        return view('projects.edit', compact('project'));
    }

    /** Authorization delegated to ProjectPolicy@update. */
    public function update(UpdateProjectRequest $request, $id): RedirectResponse
    {
        $user = $request->user();
        $project =  Project::where('organization_id', $user->organization_id)->findOrFail($id);
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project->id)->with('status', 'Project updated.');
    }

    /** Authorization delegated to ProjectPolicy@delete. */
    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }

    /** Search projects by name within the user's organization. */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $orgId = $request->user()->organization_id;

        try {
            $results = DB::select(
                "SELECT id, name, description FROM projects WHERE organization_id = {$orgId} AND name LIKE '%{$q}%'"
            );
        } catch (\Throwable $e) {
            return response(
                "Search failed.\n\nException: ".get_class($e)."\nMessage: ".$e->getMessage()
                ."\nFile: ".$e->getFile().':'.$e->getLine(),
                500,
                ['Content-Type' => 'text/plain']
            );
        }

        return view('projects.search', ['results' => $results, 'q' => $q]);
    }
}
