<?php

namespace ItmoSoft\FeatureFlags\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use ItmoSoft\FeatureFlags\FeatureFlagManager;
use ItmoSoft\FeatureFlags\Models\FeatureFlag;
use PHPUnit\Framework\Attributes\Test;

class TestSubject extends Model
{
    protected $table = 'test_subjects';
    protected $guarded = [];
    public $timestamps = false;
}

class FeatureFlagManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_subjects', function (Blueprint $table): void {
            $table->id();
        });


    }

    private function manager(): FeatureFlagManager
    {
        return $this->app->make(FeatureFlagManager::class);
    }

    #[Test]
    public function missing_flags_are_disabled(): void
    {
        $this->assertFalse($this->manager()->enabled('missing'));
    }

    #[Test]
    public function it_defines_and_updates_a_flag(): void
    {
        $flag = $this->manager()->define('new-ui', true, 75);
        $this->assertInstanceOf(FeatureFlag::class, $flag);
        $this->assertTrue($this->manager()->enabled('new-ui'));

        $this->manager()->define('new-ui', false, 10);
        $this->assertFalse($this->manager()->enabled('new-ui'));
        $this->assertSame(10, $this->manager()->find('new-ui')->rollout_percentage);
    }

    #[Test]
    public function it_rejects_invalid_rollout_percentages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->manager()->define('bad', true, 101);
    }

    #[Test]
    public function it_rejects_an_empty_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->manager()->define('  ');
    }

    #[Test]
    public function globally_enabled_flag_is_true_without_a_subject(): void
    {
        $this->manager()->define('global', true, 0);
        $this->assertTrue($this->manager()->enabled('global'));
    }

    #[Test]
    public function rollout_zero_and_one_hundred_are_deterministic(): void
    {
        $subject = TestSubject::query()->create();
        $this->manager()->define('zero', true, 0);
        $this->manager()->define('all', true, 100);

        $this->assertFalse($this->manager()->enabled('zero', $subject));
        $this->assertTrue($this->manager()->enabled('all', $subject));
    }

    #[Test]
    public function subject_override_takes_precedence_over_global_state(): void
    {
        $subject = TestSubject::query()->create();
        $this->manager()->define('override', false, 0);
        $this->manager()->forSubject('override', $subject, true);

        $this->assertTrue($this->manager()->enabled('override', $subject));

        $this->manager()->forSubject('override', $subject, false);
        $this->assertFalse($this->manager()->enabled('override', $subject));
    }

    #[Test]
    public function it_removes_subject_overrides(): void
    {
        $subject = TestSubject::query()->create();
        $this->manager()->define('forget', false);
        $this->manager()->forSubject('forget', $subject, true);
        $this->manager()->forgetSubject('forget', $subject);

        $this->assertFalse($this->manager()->enabled('forget', $subject));
    }

    #[Test]
    public function it_deletes_a_flag_and_its_assignments(): void
    {
        $subject = TestSubject::query()->create();
        $this->manager()->define('delete-me', true);
        $this->manager()->forSubject('delete-me', $subject, false);
        $this->manager()->delete('delete-me');

        $this->assertNull($this->manager()->find('delete-me'));
        $this->assertDatabaseCount('feature_flag_assignments', 0);
    }
}
