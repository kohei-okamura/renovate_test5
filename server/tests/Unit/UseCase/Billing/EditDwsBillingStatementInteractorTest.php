<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\EditDwsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatementInteractor} Test.
 */
class EditDwsBillingStatementInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $editValue;
    private EditDwsBillingStatementInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditDwsBillingStatementInteractorTest $self): void {
            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->byDefault();
            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();
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
            $self->interactor = app(EditDwsBillingStatementInteractor::class);

            $self->editValue = [
                'status' => DwsBillingStatus::fixed(),
            ];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the lookupDwsBillingStatementUseCase return empty', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingBundles[0]->id,
                    $this->examples->dwsBillingStatements[2]->id,
                )
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->dwsBillings[0]->id,
                        $this->examples->dwsBillingBundles[0]->id,
                        $this->examples->dwsBillingStatements[2]->id,
                        $this->editValue
                    );
                }
            );
        });
        $this->should('edit the DwsBillingService after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->dwsBillingStatementRepository
                        ->expects('store')
                        ->andReturn($this->examples->dwsBillingStatements[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[2]->id,
                $this->examples->dwsBillingBundles[0]->id,
                $this->examples->dwsBillingStatements[2]->id,
                $this->editValue
            );
        });
        $this->should('return the DwsCertification', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingStatements[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[2]->id,
                    $this->examples->dwsBillingBundles[0]->id,
                    $this->examples->dwsBillingStatements[2]->id,
                    $this->editValue
                )
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：明細書が更新されました',
                    ['id' => $this->examples->dwsBillingStatements[0]->id] + $context
                );

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[2]->id,
                $this->examples->dwsBillingBundles[0]->id,
                $this->examples->dwsBillingStatements[2]->id,
                $this->editValue
            );
        });
    }
}
