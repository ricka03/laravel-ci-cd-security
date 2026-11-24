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

    //strip tags - remove all <> tags from the content to prevent XSS attacks
   // $cleaned = strip_tags($request->validated('content'));
    $cleaned = ($request->validated('content'));

        $project->comments()->create([
            'content' => trim($cleaned),

            //validate the request before saving
            //if it does not fulfill the validations it will throw an error
            // 'content' => $request->validated('content'),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project)->with ('status', 'Comment posted.');
    }
}
