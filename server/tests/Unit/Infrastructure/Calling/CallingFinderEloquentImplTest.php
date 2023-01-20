<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Calling;

use Domain\Calling\Calling;
use Domain\Calling\CallingResponse;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Calling\CallingFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Calling\CallingFinderEloquentImpl} のテスト.
 */
class CallingFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private CallingFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (CallingFinderEloquentImplTest $self): void {
            $self->finder = app(CallingFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return FinderResult on Calling', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof Calling);
        });
        $this->should('return FinderResult on Calling with given invalid filter', function (): void {
            $result = $this->finder->find(['invalid' => 1234], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof Calling);
        });
        $this->should('return each Callings with given `expiredRange`', function (): void {
            $calling = $this->examples->callings[0];
            $range = CarbonRange::create([
                'start' => $calling->expiredAt->subMinute(),
                'end' => $calling->expiredAt->addMinute(),
            ]);

            $result = $this->finder->find(['expiredRange' => $range], ['sortBy' => 'id']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Calling $x): bool => $x->expiredAt->isBetween($range->start, $range->end));
        });
        $this->should('return each Callings with given `response` is false', function (): void {
            $responseIds = Seq::fromArray($this->examples->callingResponses)
                ->map(fn (CallingResponse $x): int => $x->callingId)
                ->toArray();

            $result = $this->finder->find(['response' => false], ['sortBy' => 'id']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Calling $x): bool => !in_array($x->id, $responseIds, true));
        });
    }
}
