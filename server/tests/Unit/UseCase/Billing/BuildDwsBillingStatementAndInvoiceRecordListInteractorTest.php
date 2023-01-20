<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Exchange\ExchangeRecord;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListInteractor} Test.
 */
final class BuildDwsBillingStatementAndInvoiceRecordListInteractorTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    public const TEST_FILE_PATH = 'test-file-path';

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;

    private BuildDwsBillingStatementAndInvoiceRecordListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildDwsBillingStatementAndInvoiceRecordListInteractorTest $self): void {
            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::fromArray($self->examples->dwsBillingBundles)->take(5);

            $bundle = $self->dwsBillingBundles->head();

            $self->dwsBillingInvoiceRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$bundle->id => Seq::from($self->examples->dwsBillingInvoices[0])]))
                ->byDefault();

            $self->dwsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$bundle->id => Seq::from($self->examples->dwsBillingStatements[0])]))
                ->byDefault();

            $self->interactor = app(BuildDwsBillingStatementAndInvoiceRecordListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of ExchangeRecord', function (): void {
            $records = $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);

            foreach ($records as $record) {
                $this->assertInstanceOf(ExchangeRecord::class, $record);
            }
        });
        $this->should('use DwsBillingInvoiceRepository', function (): void {
            $this->dwsBillingBundles->each(function (DwsBillingBundle $x) {
                $this->dwsBillingInvoiceRepository
                    ->expects('lookupByBundleId')
                    ->with($x->id)
                    ->andReturn(Map::from([$x->id => Seq::from($this->examples->dwsBillingInvoices[0])]));
            });

            $this->assertIsArray($this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles));
        });
        $this->should('use DwsBillingStatementFinder', function (): void {
            $this->dwsBillingBundles->each(function (DwsBillingBundle $x) {
                $this->dwsBillingStatementRepository
                    ->expects('lookupByBundleId')
                    ->with($x->id)
                    ->andReturn(Map::from([$x->id => Seq::from($this->examples->dwsBillingStatements[0])]));
            });

            $this->assertIsArray($this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles));
        });
    }
}
