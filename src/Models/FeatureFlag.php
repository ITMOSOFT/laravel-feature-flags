<?php

namespace ItmoSoft\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureFlag extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'rollout_percentage' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return config('feature-flags.tables.flags', 'feature_flags');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FeatureFlagAssignment::class, 'feature_flag_id');
    }
}
