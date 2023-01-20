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
use Domain\ServiceCode\ServiceCode;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesInteractor} のテスト.
 */
final class ResolveDwsNameFromServiceCodesInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use DwsHomeHelpServiceDictionaryEntryFinderMixin;
    use DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private Seq $serviceCodes;
    private ResolveDwsNameFromServiceCodesInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsHomeHelpServiceDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    [
                        $self->examples->dwsHomeHelpServiceDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('111111'),
                            'name' => 'サービス名称1',
                        ]),
                        $self->examples->dwsHomeHelpServiceDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('222222'),
                            'name' => 'サービス名称2',
                        ]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();
            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    [
                        $self->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('333333'),
                            'name' => 'サービス名称3',
                        ]),
                        $self->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('444444'),
                            'name' => 'サービス名称4',
                        ]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();

            $self->serviceCodes = Seq::from(
                ServiceCode::fromString('111111'),
                ServiceCode::fromString('222222'),
                ServiceCode::fromString('333333'),
                ServiceCode::fromString('444444'),
            );

            $self->interactor = app(ResolveDwsNameFromServiceCodesInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use DwsHomeHelpServiceDictionaryEntryFinder', function (): void {
            $this->dwsHomeHelpServiceDictionaryEntryFinder
                ->expects('find')
                ->with(
                    [
                        'providedIn' => Carbon::now(),
                        'serviceCodes' => $this->serviceCodes->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                    ],
                    ['all' => true, 'sortBy' => 'id', 'desc' => true]
                )
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->dwsHomeHelpServiceDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('111111'),
                            'name' => 'サービス名称1',
                        ]),
                        $this->examples->dwsHomeHelpServiceDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('222222'),
                            'name' => 'サービス名称2',
                        ]),
                    ],
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $this->serviceCodes);
        });

        $this->should('use DwsVisitingCareForPwsdDictionaryEntryFinder', function (): void {
            $this->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->expects('find')
                ->with(
                    [
                        'providedIn' => Carbon::now(),
                        'serviceCodes' => $this->serviceCodes->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                    ],
                    ['all' => true, 'sortBy' => 'id', 'desc' => true]
                )
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('333333'),
                            'name' => 'サービス名称3',
                        ]),
                        $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('444444'),
                            'name' => 'サービス名称4',
                        ]),
                    ],
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $this->serviceCodes);
        });
        $this->should('return Map from serviceCode to name', function (): void {
            $this->assertSame(
                [
                    '111111' => 'サービス名称1',
                    '222222' => 'サービス名称2',
                    '333333' => 'サービス名称3',
                    '444444' => 'サービス名称4',
                ],
                $this->interactor->handle($this->context, $this->serviceCodes)->toAssoc()
            );
        });
    }
}
