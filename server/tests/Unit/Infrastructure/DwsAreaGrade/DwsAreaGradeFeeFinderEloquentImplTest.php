<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DwsAreaGrade;

use Closure;
use Domain\Common\Carbon;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Infrastructure\DwsAreaGrade\DwsAreaGradeFeeFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\DwsAreaGrade\DwsAreaGradeFeeFinderEloquentImpl} のテスト.
 */
final class DwsAreaGradeFeeFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsAreaGradeFeeFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (DwsAreaGradeFeeFinderEloquentImplTest $self): void {
            $self->finder = app(DwsAreaGradeFeeFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $dwsAreaGradeId = $this->examples->dwsAreaGradeFees[0]->dwsAreaGradeId;
        $effectivatedOn = Carbon::parse('2021-02-03');
        $examples = [
            'specified dwsAreaGradeId' => [
                ['dwsAreaGradeId' => $dwsAreaGradeId],
                fn (DwsAreaGradeFee $x): bool => $x->dwsAreaGradeId <= $dwsAreaGradeId,
            ],
            'specified effectivatedBefore' => [
                ['effectivatedBefore' => $effectivatedOn],
                fn (DwsAreaGradeFee $x): bool => $x->effectivatedOn <= $effectivatedOn,
            ],
        ];
        $this->should(
            'return a FinderResult of DwsAreaGradeFee with given parameters',
            function (array $condition, Closure $assert): void {
                $actual = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
                $this->assertExists($this->examples->dwsAreaGradeFees, $this->invert($assert));
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of DwsAreaGradeFee with unknown parameter',
            function (): void {
                $actual = $this->finder->find(
                    ['invalid' => 'value'],
                    ['all' => true, 'sortBy' => 'id'],
                );

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($this->examples->dwsAreaGradeFees, $actual->list->toArray());
            }
        );
        $this->should(
            'return a FinderResult of DwsAreaGradeFee with given sortBy',
            function (string $sortBy, Closure $sort): void {
                $expects = Seq::fromArray($this->examples->dwsAreaGradeFees)
                    ->sortBy($sort);
                $actual = $this->finder->find([], ['all' => true, 'sortBy' => $sortBy]);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($expects->toArray(), $actual->list->toArray());
            },
            [
                'examples' => [
                    'effectivatedOn' => [
                        'effectivatedOn',
                        fn (DwsAreaGradeFee $x): Carbon => $x->effectivatedOn,
                    ],
                ],
            ]
        );
    }
}
