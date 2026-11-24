<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Requests\StoreProjectCommentRequest;

/**
 * LAB 3 (intentionally vulnerable): no structured validation and no authorization on the project.
 */
class CommentController extends Controller
{
    public function store(StoreProjectCommentRequest $request, Project $project): RedirectResponse
    {

    // strip_tags (remove all <>) // Strip all tags
     
      //  $cleaned = strip_tags($request->validated('content'));
        $cleaned = $request->validated('content');
        // trim (buang white space)

        $project->comments()->create([
            'content' => trim($cleaned), 
            # Validate the request before saving 
            # If it does not fulfill the validation it will throw an error
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Comment posted.');
    }
}
