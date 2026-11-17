<?php

namespace ItmoSoft\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlagAssignment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function getTable(): string
    {
        return config('feature-flags.tables.assignments', 'feature_flag_assignments');
    }

    public function flag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }
}
