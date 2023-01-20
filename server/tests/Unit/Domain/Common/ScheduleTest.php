<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Schedule のテスト
 */
class ScheduleTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Schedule $schedule;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ScheduleTest $self): void {
            $self->values = [
                'date' => Carbon::parse('2018-02-28 23:59:59'),
                'start' => Carbon::parse('2018-02-28 23:59:59'),
                'end' => Carbon::parse('2018-02-30 23:59:59'),
            ];
            $self->schedule = Schedule::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have date attribute', function (): void {
            $this->assertSame($this->schedule->get('date'), Arr::get($this->values, 'date'));
        });
        $this->should('have start attribute', function (): void {
            $this->assertSame($this->schedule->get('start'), Arr::get($this->values, 'start'));
        });
        $this->should('have end attribute', function (): void {
            $this->assertSame($this->schedule->get('end'), Arr::get($this->values, 'end'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->schedule);
        });
    }
}
