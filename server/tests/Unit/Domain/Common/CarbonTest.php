<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Common\Carbon} のテスト.
 */
final class CarbonTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    protected Carbon $carbon;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getNextBusinessDay(): void
    {
        $this->should('return next day when next of the day is business day', function (): void {
            $thursday = Carbon::parse('2021-10-07');

            $expected = Carbon::parse('2021-10-08');
            $this->assertSame(
                $expected->toDateString(),
                $thursday->getNextBusinessDay()->toDateString()
            );
        });
        $this->should('return three days after when next of the day is Saturday', function (): void {
            $friday = Carbon::parse('2021-10-08');

            $expected = Carbon::parse('2021-10-11');
            $this->assertSame(
                $expected->toDateString(),
                $friday->getNextBusinessDay()->toDateString()
            );
        });
        $this->should('return two days after when next of the day is Sunday', function (): void {
            $saturday = Carbon::parse('2021-10-09');

            $expected = Carbon::parse('2021-10-11');
            $this->assertSame(
                $expected->toDateString(),
                $saturday->getNextBusinessDay()->toDateString()
            );
        });
        $this->should('return two days after when next of the day is holiday', function (): void {
            $dayBeforeCultureDay = Carbon::parse('2021-11-02');

            $expected = Carbon::parse('2021-11-04');
            $this->assertSame(
                $expected->toDateString(),
                $dayBeforeCultureDay->getNextBusinessDay()->toDateString()
            );
        });
    }
}
