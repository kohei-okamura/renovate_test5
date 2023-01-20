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
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesInteractor} のテスト.
 */
final class ResolveLtcsNameFromServiceCodesInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private Seq $serviceCodes;
    private ResolveLtcsNameFromServiceCodesInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    [
                        $self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('111111'),
                            'name' => 'サービス名称1',
                        ]),
                        $self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('222222'),
                            'name' => 'サービス名称2',
                        ]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();

            $self->serviceCodes = Seq::from(
                ServiceCode::fromString('111111'),
                ServiceCode::fromString('222222')
            );

            $self->interactor = app(ResolveLtcsNameFromServiceCodesInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('指定された年月を条件にサービスコード辞書エントリを取得する', function (): void {
            $providedIn = Carbon::parse('2020-01-01');
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with(
                    [
                        'providedIn' => $providedIn,
                        'serviceCodes' => $this->serviceCodes->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                    ],
                    ['all' => true, 'sortBy' => 'id', 'desc' => true]
                )
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('111111'),
                            'name' => 'サービス名称1',
                        ]),
                        $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy([
                            'serviceCode' => ServiceCode::fromString('222222'),
                            'name' => 'サービス名称2',
                        ]),
                    ],
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $this->serviceCodes, $providedIn);
        });
        $this->should('return Map from serviceCode to name', function (): void {
            $this->assertSame(
                [
                    '111111' => 'サービス名称1',
                    '222222' => 'サービス名称2',
                ],
                $this->interactor->handle($this->context, $this->serviceCodes, Carbon::now())->toAssoc()
            );
        });
    }
}
