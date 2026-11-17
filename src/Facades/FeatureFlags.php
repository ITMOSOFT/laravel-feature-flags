<?php

namespace ItmoSoft\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ItmoSoft\FeatureFlags\Models\FeatureFlag define(string $key, bool $enabled = false, int $rolloutPercentage = 100)
 * @method static bool enabled(string $key, ?\Illuminate\Database\Eloquent\Model $subject = null)
 * @method static void forSubject(string $key, \Illuminate\Database\Eloquent\Model $subject, bool $enabled)
 * @method static void forgetSubject(string $key, \Illuminate\Database\Eloquent\Model $subject)
 * @method static ?\ItmoSoft\FeatureFlags\Models\FeatureFlag find(string $key)
 * @method static void delete(string $key)
 */
class FeatureFlags extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'feature-flags';
    }
}
