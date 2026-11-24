<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * LAB 3 — Activities 1-2: safe query construction.
 *
 * The keyword is passed to the query builder as a BOUND PARAMETER, so it can
 * never be parsed as SQL. Errors return a generic message; the detail goes to
 * the log, not to the client.
 */
class ProjectSearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        // 'nullable' means the key may be absent entirely, so default it.
        $keyword = $validated['q'] ?? '';

        try {
            $projects = Project::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('name', 'like', '%'.$keyword.'%')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString();
        } catch (\Throwable $e) {
            // Never return exception class, message, file or line to the client.
            Log::error('Project search failed', ['exception' => $e]);

            return response('Search is temporarily unavailable.', 500);
        }

        return view('projects.search', [
            'projects' => $projects,
            'keyword' => $keyword,
        ]);
    }
}
