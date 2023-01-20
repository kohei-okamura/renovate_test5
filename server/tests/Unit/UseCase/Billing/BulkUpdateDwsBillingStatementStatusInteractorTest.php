<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BulkUpdateDwsBillingStatementStatusInteractor;

/**
 * {@link \UseCase\Billing\BulkUpdateDwsBillingStatementStatusInteractor} のテスト.
 */
final class BulkUpdateDwsBillingStatementStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use DwsBillingStatementRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBillingStatement $statement;
    private BulkUpdateDwsBillingStatementStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingStatements[0])
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

            $self->statement = $self->examples->dwsBillingStatements[0];
            $self->interactor = app(BulkUpdateDwsBillingStatementStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the DwsBillingStatement after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->simpleLookupDwsBillingStatementUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::updateBillings(),
                            $this->statement->id,
                        )
                        ->andReturn(Seq::from($this->statement));
                    $this->dwsBillingStatementRepository
                        ->expects('store')
                        ->with(equalTo($this->statement->copy([
                            'status' => DwsBillingStatus::fixed(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->statement);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->statement->dwsBillingId,
                [$this->statement->id],
                DwsBillingStatus::fixed()
            );
        });
        $this->should(
            'throw a NotFoundException when SimpleLookupDwsBillingStatementUseCase return empty Seq',
            function (): void {
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        Permission::updateBillings(),
                        $this->statement->id
                    )
                    ->andReturn(Seq::empty());

                $this->assertThrows(
                    NotFoundException::class,
                    function (): void {
                        $this->interactor->handle(
                            $this->context,
                            $this->statement->dwsBillingId,
                            [$this->statement->id],
                            DwsBillingStatus::fixed()
                        );
                    }
                );
            }
        );
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：明細書が更新されました', ['id' => ''] + $context);

            $this->interactor->handle(
                $this->context,
                $this->statement->dwsBillingId,
                [$this->statement->id],
                DwsBillingStatus::fixed()
            );
        });
    }
}
