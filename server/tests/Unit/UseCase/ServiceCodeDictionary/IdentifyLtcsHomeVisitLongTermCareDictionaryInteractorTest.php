<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor} のテスト.
 */
final class IdentifyLtcsHomeVisitLongTermCareDictionaryInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsHomeVisitLongTermCareDictionaryFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyLtcsHomeVisitLongTermCareDictionaryInteractorTest $self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->ltcsHomeVisitLongTermCareDictionaries[0]),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Option of Dictionary', function (): void {
            $actual = $this->interactor->handle($this->context, Carbon::now());

            $this->assertNotEmpty($actual);
            $this->assertInstanceOf(LtcsHomeVisitLongTermCareDictionary::class, $actual->get());
        });
        $this->should('call finder with specified parameters', function (): void {
            $this->ltcsHomeVisitLongTermCareDictionaryFinder
                ->expects('find')
                ->with(
                    equalTo(['effectivatedBefore' => Carbon::now()]),
                    equalTo(['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => true])
                )
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->ltcsHomeVisitLongTermCareDictionaries[0]),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, Carbon::now());
        });
    }
}
