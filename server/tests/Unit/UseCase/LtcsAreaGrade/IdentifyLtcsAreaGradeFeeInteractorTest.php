<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsAreaGrade;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsAreaGradeFeeFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeInteractor;

/**
 * {@link \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeInteractor} のテスト.
 */
final class IdentifyLtcsAreaGradeFeeInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsAreaGradeFeeFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyLtcsAreaGradeFeeInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyLtcsAreaGradeFeeInteractorTest $self): void {
            $self->ltcsAreaGradeFeeFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->ltcsAreaGradeFees[0]),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyLtcsAreaGradeFeeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Option of LtcsAreaGradeFee', function (): void {
            $ltcsAreaGradeId = $this->examples->ltcsAreaGradeFees[0]->ltcsAreaGradeId;
            $actual = $this->interactor->handle($this->context, $ltcsAreaGradeId, Carbon::now());

            $this->assertNotEmpty($actual);
            $this->assertInstanceOf(LtcsAreaGradeFee::class, $actual->get());
        });
        $this->should('call finder with specified parameters', function (): void {
            $ltcsAreaGradeId = $this->examples->ltcsAreaGradeFees[0]->ltcsAreaGradeId;
            $filterParams = [
                'ltcsAreaGradeId' => $ltcsAreaGradeId,
                'effectivatedBefore' => Carbon::now(),
            ];
            $paginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'effectivatedOn',
                'desc' => true,
            ];
            $this->ltcsAreaGradeFeeFinder
                ->expects('find')
                ->with(equalTo($filterParams), equalTo($paginationParams))
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->ltcsAreaGradeFees[0]),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $ltcsAreaGradeId, Carbon::now());
        });
    }
}
