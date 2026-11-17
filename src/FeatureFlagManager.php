<?php

namespace ItmoSoft\FeatureFlags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ItmoSoft\FeatureFlags\Models\FeatureFlag;
use ItmoSoft\FeatureFlags\Models\FeatureFlagAssignment;

class FeatureFlagManager
{
    public function define(string $key, bool $enabled = false, int $rolloutPercentage = 100): FeatureFlag
    {
        $this->validateKey($key);

        if ($rolloutPercentage < 0 || $rolloutPercentage > 100) {
            throw new InvalidArgumentException('Rollout percentage must be between 0 and 100.');
        }

        return FeatureFlag::query()->updateOrCreate(
            ['key' => $key],
            ['enabled' => $enabled, 'rollout_percentage' => $rolloutPercentage],
        );
    }

    public function enabled(string $key, ?Model $subject = null): bool
    {
        $flag = $this->find($key);

        if ($flag === null) {
            return false;
        }

        if ($subject !== null) {
            $override = FeatureFlagAssignment::query()
                ->where('feature_flag_id', $flag->getKey())
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', (string) $subject->getKey())
                ->value('enabled');

            if ($override !== null) {
                return (bool) $override;
            }
        }

        if (! $flag->enabled) {
            return false;
        }

        if ($subject === null) {
            return true;
        }

        if ($flag->rollout_percentage <= 0) {
            return false;
        }

        if ($flag->rollout_percentage >= 100) {
            return true;
        }

        $identity = $flag->key.'|'.$subject->getMorphClass().'|'.$subject->getKey();
        $bucket = hexdec(substr(hash('sha256', $identity), 0, 8)) % 100;

        return $bucket < $flag->rollout_percentage;
    }

    public function forSubject(string $key, Model $subject, bool $enabled): void
    {
        $flag = $this->find($key);

        if ($flag === null) {
            throw new InvalidArgumentException("Feature flag [{$key}] does not exist. Define it first.");
        }

        FeatureFlagAssignment::query()->updateOrCreate(
            [
                'feature_flag_id' => $flag->getKey(),
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => (string) $subject->getKey(),
            ],
            ['enabled' => $enabled],
        );
    }

    public function forgetSubject(string $key, Model $subject): void
    {
        $flag = $this->find($key);

        if ($flag === null) {
            return;
        }

        FeatureFlagAssignment::query()
            ->where('feature_flag_id', $flag->getKey())
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', (string) $subject->getKey())
            ->delete();
    }

    public function find(string $key): ?FeatureFlag
    {
        return FeatureFlag::query()->where('key', $key)->first();
    }

    public function delete(string $key): void
    {
        $flag = $this->find($key);

        if ($flag === null) {
            return;
        }

        DB::transaction(function () use ($flag): void {
            $flag->assignments()->delete();
            $flag->delete();
        });
    }

    private function validateKey(string $key): void
    {
        if (trim($key) === '') {
            throw new InvalidArgumentException('Feature flag key cannot be empty.');
        }
    }
}
