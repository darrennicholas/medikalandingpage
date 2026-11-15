<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    /** @param array<string, list<string>> $permissions */
    public function hasPermission(string $module, string $action, array $permissions = []): bool
    {
        $grants = $permissions !== [] ? $permissions : ($this->permissions ?? []);

        return in_array($action, $grants[$module] ?? [], true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
