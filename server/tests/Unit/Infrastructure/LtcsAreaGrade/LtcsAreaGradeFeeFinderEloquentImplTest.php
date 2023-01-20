<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\LtcsAreaGrade;

use Closure;
use Domain\Common\Carbon;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeFinderEloquentImpl} のテスト.
 */
final class LtcsAreaGradeFeeFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsAreaGradeFeeFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsAreaGradeFeeFinderEloquentImplTest $self): void {
            $self->finder = app(LtcsAreaGradeFeeFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $ltcsAreaGradeId = $this->examples->ltcsAreaGradeFees[0]->ltcsAreaGradeId;
        $effectivatedOn = Carbon::parse('2021-02-03');
        $examples = [
            'specified ltcsAreaGradeId' => [
                ['ltcsAreaGradeId' => $ltcsAreaGradeId],
                fn (LtcsAreaGradeFee $x): bool => $x->ltcsAreaGradeId <= $ltcsAreaGradeId,
            ],
            'specified effectivatedBefore' => [
                ['effectivatedBefore' => $effectivatedOn],
                fn (LtcsAreaGradeFee $x): bool => $x->effectivatedOn <= $effectivatedOn,
            ],
        ];
        $this->should(
            'return a FinderResult of LtcsAreaGradeFee with given parameters',
            function (array $condition, Closure $assert): void {
                $actual = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
                $this->assertExists($this->examples->ltcsAreaGradeFees, $this->invert($assert));
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of LtcsAreaGradeFee with unknown parameter',
            function (): void {
                $actual = $this->finder->find(
                    ['invalid' => 'value'],
                    ['all' => true, 'sortBy' => 'id'],
                );

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($this->examples->ltcsAreaGradeFees, $actual->list->toArray());
            }
        );
        $this->should(
            'return a FinderResult of DwsAreaGradeFee with given sortBy',
            function (string $sortBy, Closure $sort): void {
                $expects = Seq::fromArray($this->examples->ltcsAreaGradeFees)
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
                        fn (LtcsAreaGradeFee $x): Carbon => $x->effectivatedOn,
                    ],
                ],
            ]
        );
    }
}
