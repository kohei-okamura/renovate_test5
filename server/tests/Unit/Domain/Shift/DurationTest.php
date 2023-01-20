<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Shift\Duration;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Duration のテスト
 */
class DurationTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Duration $duration;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DurationTest $self): void {
            $self->values = [
                'activity' => '食事介助',
                'duration' => 1,
            ];
            $self->duration = Duration::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have activity attribute', function (): void {
            $this->assertSame($this->duration->get('activity'), Arr::get($this->values, 'activity'));
        });
        $this->should('have duration attribute', function (): void {
            $this->assertSame($this->duration->get('duration'), Arr::get($this->values, 'duration'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->duration);
        });
    }
}
