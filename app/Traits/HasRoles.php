<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection as SupportCollection;

trait HasRoles
{
    /**
     * Roles assigned to the model.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Assign the given role to the model.
     */
    public function assignRole(string $role): Role
    {
        $roleModel = Role::query()->where('name', $role)->firstOrFail();

        $this->roles()->syncWithoutDetaching([$roleModel->getKey()]);

        return $roleModel;
    }

    /**
     * Determine if the model has the given role.
     *
     * @param  string|Role|SupportCollection<int, Role>|Collection<int, Role>  $role
     */
    public function hasRole(string|Role|SupportCollection|Collection $role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        if ($role instanceof Role) {
            return $this->roles->contains($role);
        }

        return $role->intersect($this->roles)->isNotEmpty();
    }
}
