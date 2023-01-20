<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Exception;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingFinderMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingInvoiceInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingInvoiceInteractor} のテスト.
 */
final class UpdateDwsBillingInvoiceInteractorTest extends Test
{
    use BuildDwsBillingInvoiceUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingFinderMixin;
    use DwsBillingStatementRepositoryMixin;
    use DwsBillingTestSupport;
    use LookupDwsBillingBundleUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private UpdateDwsBillingInvoiceInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingInvoiceRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingInvoice $x): DwsBillingInvoice => $x->copy(['id' => 1]))
                ->byDefault();

            $self->dwsBillingInvoiceRepository
                ->allows('lookupByBundleId')
                ->andReturn(Seq::from($self->invoice)->toMap('dwsBillingBundleId')->groupBy(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId))
                ->byDefault();

            $self->buildDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->invoice)
                ->byDefault();

            $self->dwsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn($self->statements->toMap('dwsBillingBundleId')->groupBy(fn (DwsBillingStatement $x): int => $x->dwsBillingBundleId))
                ->byDefault();

            $self->dwsBillingFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->billing), Pagination::create()))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->bundle))
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingInvoiceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn(DwsBillingInvoice::create([]));
            $this->dwsBillingInvoiceRepository->expects('store')->never();

            $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
        });
        $this->should('throw Exception when DwsBillingInvoiceRepository throws it', function (): void {
            $this->dwsBillingInvoiceRepository->expects('store')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
            });
        });
        $this->should('throw NotFoundException when the DwsBillingStatement not found', function (): void {
            $this->dwsBillingStatementRepository->allows('lookupByBundleId')->andReturn(Map::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
            });
        });
        $this->should('throw NotFoundException when the DwsBillingInvoice not found', function (): void {
            $this->dwsBillingInvoiceRepository->allows('lookupByBundleId')->andReturn(Map::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
            });
        });
        $this->should('throw NotFoundException when the DwsBillingBundle not found', function (): void {
            $this->lookupDwsBillingBundleUseCase->allows('handle')->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
            });
        });
        $this->should('lookupByBundleId DwsBillingInvoiceRepository', function (): void {
            $this->dwsBillingInvoiceRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Seq::from($this->invoice)->toMap('dwsBillingBundleId')->groupBy(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId));

            $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
        });
        $this->should('lookupByBundleId DwsBillingStatementRepository ', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn($this->statements->toMap('dwsBillingBundleId')->groupBy(fn (DwsBillingStatement $x): int => $x->dwsBillingBundleId));

            $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
        });
        $this->should('handle LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billing->id, $this->bundle->id)
                ->andReturn(Seq::from($this->bundle));

            $this->interactor->handle($this->context, $this->bundle->id, $this->billing->id);
        });
    }
}
