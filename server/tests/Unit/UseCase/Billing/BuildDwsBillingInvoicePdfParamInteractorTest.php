<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoicePdf;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatementPdf;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Entity;
use Domain\ServiceCode\ServiceCode;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ResolveDwsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor} のテスト.
 */
final class BuildDwsBillingInvoicePdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingStatementRepositoryMixin;
    use MockeryMixin;
    use ResolveDwsNameFromServiceCodesUseCaseMixin;
    use UnitSupport;

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;
    private BuildDwsBillingInvoicePdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildDwsBillingInvoicePdfParamInteractorTest $self): void {
            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::fromArray($self->examples->dwsBillingBundles);
            $bundleId = $self->dwsBillingBundles->head()->id;

            $self->dwsBillingInvoiceRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$bundleId => Seq::fromArray($self->examples->dwsBillingInvoices)]))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$bundleId => Seq::fromArray($self->examples->dwsBillingStatements)]))
                ->byDefault();
            $self->resolveDwsNameFromServiceCodesUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::from(
                        ...$self->examples->dwsHomeHelpServiceDictionaryEntries,
                        ...$self->examples->dwsVisitingCareForPwsdDictionaryEntries
                    )
                        ->toMap(fn (Entity $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (Entity $x): string => $x->name)
                )
                ->byDefault();

            $self->interactor = app(BuildDwsBillingInvoicePdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('get DwsBillingInvoice', function (): void {
            $billing = $this->dwsBilling;
            $bundleId = $this->dwsBillingBundles->head()->id;
            $this->dwsBillingInvoiceRepository
                ->expects('lookupByBundleId')
                ->with($bundleId)
                ->andReturn(Map::from([$bundleId => Seq::fromArray($this->examples->dwsBillingInvoices)]));

            $this->interactor
                ->handle($this->context, $billing, $this->dwsBillingBundles)['bundles']
                ->toArray();
        });
        $this->should('get DwsBillingStatement', function (): void {
            $billing = $this->dwsBilling;
            $bundleId = $this->dwsBillingBundles->head()->id;
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($bundleId)
                ->andReturn(Map::from([$bundleId => Seq::fromArray($this->examples->dwsBillingStatements)]));

            $this->interactor
                ->handle($this->context, $billing, $this->dwsBillingBundles)['bundles']
                ->toArray();
        });
        $this->should('use resolveDwsNameFromServiceCodesUseCase', function (): void {
            $billing = $this->dwsBilling;

            Seq::fromArray($this->examples->dwsBillingBundles)
                ->each(function (DwsBillingBundle $bundle): void {
                    Seq::fromArray($this->examples->dwsBillingStatements)
                        ->each(function (DwsBillingStatement $x): void {
                            $serviceCodes = Seq::fromArray($x->items)
                                ->filter(
                                    fn (DwsBillingStatementItem $item): bool => $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::homeHelpService()->value()
                                        || $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::visitingCareForPwsd()->value()
                                )
                                ->map(fn (DwsBillingStatementItem $item): ServiceCode => $item->serviceCode);

                            $this->resolveDwsNameFromServiceCodesUseCase
                                ->expects('handle')
                                ->with($this->context, equalTo($serviceCodes->computed()))
                                ->andReturn(
                                    Seq::from(...$this->examples->dwsHomeHelpServiceDictionaryEntries, ...$this->examples->dwsVisitingCareForPwsdDictionaryEntries)
                                        ->toMap(fn (Entity $x): string => $x->serviceCode->toString())
                                        ->mapValues(fn (Entity $x): string => $x->name)
                                );
                        });
                });

            $this->interactor
                ->handle($this->context, $billing, $this->dwsBillingBundles)['bundles']
                ->each(function (array $x): void {
                    $x['statements']->toArray();
                });
        });
        $this->should('return an array of params for dws invoice pdf', function (): void {
            $billing = $this->dwsBilling;

            $serviceCodeMap = Seq::from(
                ...$this->examples->dwsHomeHelpServiceDictionaryEntries,
                ...$this->examples->dwsVisitingCareForPwsdDictionaryEntries
            )
                ->groupBy(fn (Entity $x): string => $x->serviceCode->toString())
                ->mapValues(fn (Seq $x): string => $x->head()->name);
            $expected = [
                'bundles' => $this->dwsBillingBundles->map(fn (DwsBillingBundle $bundle) => [
                    'invoices' => Seq::fromArray($this->examples->dwsBillingInvoices)
                        ->map(fn (DwsBillingInvoice $invoice): DwsBillingInvoicePdf => DwsBillingInvoicePdf::from($billing, $bundle, $invoice)),
                    'statements' => Seq::fromArray($this->examples->dwsBillingStatements)
                        ->map(fn (DwsBillingStatement $statement): DwsBillingStatementPdf => DwsBillingStatementPdf::from($billing, $bundle, $statement, $serviceCodeMap)),
                ]),
            ];

            $actual = $this->interactor->handle($this->context, $billing, $this->dwsBillingBundles);
            $this->assertEquals(
                $expected,
                $actual
            );
            $this->assertEach(
                function (array $expectedValue, array $actualValue): void {
                    $this->assertArrayStrictEquals($expectedValue['invoices']->toArray(), $actualValue['invoices']->toArray());
                    $this->assertArrayStrictEquals($expectedValue['statements']->toArray(), $actualValue['statements']->toArray());
                },
                $expected['bundles']->toArray(),
                $actual['bundles']->toArray()
            );
        });
    }
}
