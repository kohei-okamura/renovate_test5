<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingInvoice;
use Exception;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingInvoiceInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingInvoiceInteractor} のテスト.
 */
final class CreateDwsBillingInvoiceInteractorTest extends Test
{
    use BuildDwsBillingInvoiceUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateDwsBillingInvoiceInteractor $interactor;

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

            $self->buildDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->invoice)
                ->byDefault();

            $self->interactor = app(CreateDwsBillingInvoiceInteractor::class);
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

            $this->interactor->handle($this->context, $this->bundle, $this->statements);
        });
        $this->should('throw Exception when DwsBillingInvoiceRepository throws it', function (): void {
            $this->dwsBillingInvoiceRepository->expects('store')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->bundle, $this->statements);
            });
        });
        $this->should('use BuildDwsBillingInvoiceUseCase', function (): void {
            $this->buildDwsBillingInvoiceUseCase
                ->expects('handle')
                ->with($this->context, $this->bundle, $this->statements)
                ->andReturn($this->invoice)
                ->byDefault();

            $this->interactor->handle($this->context, $this->bundle, $this->statements);
        });
    }
}
