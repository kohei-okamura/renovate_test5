<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BulkUpdateLtcsBillingStatementStatusInteractor;

/**
 * {@link \UseCase\Billing\BulkUpdateLtcsBillingStatementStatusInteractor} のテスト.
 */
final class BulkUpdateLtcsBillingStatementStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupLtcsBillingStatementUseCaseMixin;
    use LtcsBillingStatementRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsBillingStatement $statement;
    private BulkUpdateLtcsBillingStatementStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsBillingStatements[0])
                ->byDefault();
            $self->ltcsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingStatements[0]))
                ->byDefault();

            $self->statement = $self->examples->ltcsBillingStatements[0];
            $self->interactor = app(BulkUpdateLtcsBillingStatementStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the LtcsBillingStatement after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupLtcsBillingStatementUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::updateBillings(),
                            $this->statement->billingId,
                            $this->statement->bundleId,
                            $this->statement->id,
                        )
                        ->andReturn(Seq::from($this->statement));
                    $this->ltcsBillingStatementRepository
                        ->expects('store')
                        ->with(equalTo($this->statement->copy([
                            'status' => LtcsBillingStatus::fixed(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->statement);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->statement->billingId,
                $this->statement->bundleId,
                [$this->statement->id],
                LtcsBillingStatus::fixed()
            );
        });
        $this->should('throw a NotFoundException when LookupLtcsBillingStatementUseCase return empty Seq', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->statement->billingId, $this->statement->bundleId, $this->statement->id)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->statement->billingId,
                        $this->statement->bundleId,
                        [$this->statement->id],
                        LtcsBillingStatus::fixed()
                    );
                }
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：明細書が更新されました', ['id' => ''] + $context);

            $this->interactor->handle(
                $this->context,
                $this->statement->billingId,
                $this->statement->bundleId,
                [$this->statement->id],
                LtcsBillingStatus::fixed()
            );
        });
    }
}
