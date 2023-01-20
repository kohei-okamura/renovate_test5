<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryInteractor} Test.
 */
class IdentifyDwsHomeHelpServiceDictionaryInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsHomeHelpServiceDictionaryFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyDwsHomeHelpServiceDictionaryInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (IdentifyDwsHomeHelpServiceDictionaryInteractorTest $self): void {
            $self->dwsHomeHelpServiceDictionaryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->dwsHomeHelpServiceDictionaries[0]),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyDwsHomeHelpServiceDictionaryInteractor::class);
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
            $this->assertInstanceOf(DwsHomeHelpServiceDictionary::class, $actual->get());
        });
        $this->should('call finder with specified parameters', function (): void {
            $this->dwsHomeHelpServiceDictionaryFinder
                ->expects('find')
                ->with(
                    equalTo(['effectivatedBefore' => Carbon::now()]),
                    equalTo(['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => true])
                )
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->dwsHomeHelpServiceDictionaries[0]),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, Carbon::now());
        });
    }
}
