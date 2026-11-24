<?php

use App\Models\Project;
use App\Models\User;

it('treats SQL injection input as data', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Normal Project',
    ]);

    $response = $this->actingAs($user)
        ->get(route('projects.search', [
            'q' => "' OR 1=1 -- ",
        ]));

    $response->assertOk() // Response 200
        ->assertDontSee('Normal Project'); // SHould not see "Normal Project" in the page
});

it('escapes stored XSS content', function () {
$user = User::factory()->create();
$project = Project::factory()->create([
'organization_id' => $user->organization_id,
]);

$payload = '<script>alert("XSS")</script>';
$project->comments()->create([
    'user_id' => $user->id,
    'content' => $payload,
]);

$response = $this->actingAs($user)
    ->get(route('projects.show', $project));

$response->assertOk()
    ->assertDontSee($payload, false) // I should not see the exact value of the script
    ->assertSee(e($payload), false);   // I should see the escaped value of the script
});
