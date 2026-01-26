<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Может ли пользователь просматривать проект
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Может ли пользователь обновлять проект
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Может ли пользователь удалять проект
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
