<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\None;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\VisitingCareForPwsdCalcSpecFinderMixin;
use Tests\Unit\Test;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecInteractor;

/**
 * {@link \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecInteractor} のテスト.
 */
final class IdentifyVisitingCareForPwsdCalcSpecInteractorTest extends Test
{
    use DummyContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use VisitingCareForPwsdCalcSpecFinderMixin;

    private IdentifyVisitingCareForPwsdCalcSpecInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(IdentifyVisitingCareForPwsdCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('return Some of VisitingCareForPwsdCalcSpec when it exists', function (): void {
            $spec = $this->examples->visitingCareForPwsdCalcSpecs[0];
            $office = $this->examples->offices[0];
            $targetDate = Carbon::create(2021, 2, 13);
            $filterParams = [
                'officeId' => $office->id,
                'period' => $targetDate,
            ];
            $paginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'id',
                'desc' => true,
            ];
            $this->visitingCareForPwsdCalcSpecFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(Seq::from($spec), Pagination::create([])));

            $actual = $this->interactor->handle($this->context, $office, $targetDate);

            $this->assertInstanceOf(Some::class, $actual);
            $this->assertSame($spec, $actual->get());
        });
        $this->should('return None when it is not exists', function (): void {
            $office = $this->examples->offices[0];
            $targetDate = Carbon::create(2021, 2, 13);
            $this->visitingCareForPwsdCalcSpecFinder
                ->expects('find')
                ->andReturn(FinderResult::from(Option::none(), Pagination::create([])));

            $actual = $this->interactor->handle($this->context, $office, $targetDate);

            $this->assertInstanceOf(None::class, $actual);
        });
    }
}
