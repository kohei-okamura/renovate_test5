<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsAreaGrade;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsAreaGradeFeeFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeInteractor;

/**
 * {@link \UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeInteractor} のテスト.
 */
final class IdentifyDwsAreaGradeFeeInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsAreaGradeFeeFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyDwsAreaGradeFeeInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyDwsAreaGradeFeeInteractorTest $self): void {
            $self->dwsAreaGradeFeeFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->dwsAreaGradeFees[0]),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyDwsAreaGradeFeeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Option of DwsAreaGradeFee', function (): void {
            $dwsAreaGradeId = $this->examples->dwsAreaGradeFees[0]->dwsAreaGradeId;
            $actual = $this->interactor->handle($this->context, $dwsAreaGradeId, Carbon::now());

            $this->assertNotEmpty($actual);
            $this->assertInstanceOf(DwsAreaGradeFee::class, $actual->get());
        });
        $this->should('call finder with specified parameters', function (): void {
            $dwsAreaGradeId = $this->examples->dwsAreaGradeFees[0]->dwsAreaGradeId;
            $filterParams = [
                'dwsAreaGradeId' => $dwsAreaGradeId,
                'effectivatedBefore' => Carbon::now(),
            ];
            $paginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'effectivatedOn',
                'desc' => true,
            ];
            $this->dwsAreaGradeFeeFinder
                ->expects('find')
                ->with(equalTo($filterParams), equalTo($paginationParams))
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->dwsAreaGradeFees[0]),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $dwsAreaGradeId, Carbon::now());
        });
    }
}
