<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{

    // Member can only view project within it's organization
    public function view(User $user, Project $project): bool
    {
        return $user->organization_id === $project->organization_id;
    }

    // Admin can only update within it's organization
    public function update(User $user, Project $project): bool
    {
        // Maybe needs to add organization_id policy as well
        // any admin can edit
        // CHeck can admin alpha (1,2,3 )> update project (4,5,6)
        
        return $user->organization_id === $project->organization_id && $user->role === 'admin';
        
    }
    // Admin can only delete within it's organization
    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin'
            && $user->organization_id === $project->organization_id;
    }
}
