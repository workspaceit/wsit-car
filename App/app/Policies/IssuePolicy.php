<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Issue;
use Illuminate\Auth\Access\HandlesAuthorization;

class IssuePolicy
{
    use HandlesAuthorization;

    /**
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->type === User::TYPE_SUPER_ADMIN) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function view(User $user, Issue $issue): bool
    {
        return $user->id === $issue->created_by;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function update(User $user, Issue $issue): bool
    {
        return $user->id === $issue->created_by;
    }

    /**
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function delete(User $user, Issue $issue): bool
    {
        return $user->id === $issue->created_by;
    }

    /**
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function restore(User $user, Issue $issue): bool
    {
        return false;
    }

    /**
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function forceDelete(User $user, Issue $issue): bool
    {
        return false;
    }
}
