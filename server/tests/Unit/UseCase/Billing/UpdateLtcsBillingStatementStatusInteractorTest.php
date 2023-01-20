<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateLtcsBillingStatementStatusInteractor;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatementStatusInteractor} のテスト.
 */
final class UpdateLtcsBillingStatementStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use GetLtcsBillingStatementInfoUseCaseMixin;
    use LoggerMixin;
    use LookupLtcsBillingStatementUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;
    private LtcsBillingStatement $statement;
    private array $infoArray;

    private UpdateLtcsBillingStatementStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[1];
            $self->statement = $self->examples->ltcsBillingStatements[2];
            $self->infoArray = ['response-able' => true, 'billing' => $self->examples->ltcsBillings[0]];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->statement))
                ->byDefault();
            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->statement)
                ->byDefault();
            $self->ltcsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getLtcsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->infoArray)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(UpdateLtcsBillingStatementStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return response-able array', function (): void {
            $expected = $this->infoArray;

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id,
                    LtcsBillingStatus::ready(),
                    function (LtcsBilling $x): void {
                    }
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupLtcsBillingStatementUseCase', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id
                )
                ->andReturn(Seq::from($this->statement));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id,
                    LtcsBillingStatus::fixed(),
                    function (LtcsBilling $x): void {
                    }
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            $this->bundle->id,
                            $this->statement->id,
                            LtcsBillingStatus::fixed(),
                            function (LtcsBilling $x): void {
                            }
                        );
                }
            );
        });
        $this->should('use LtcsBillingStatementRepository for updating entity', function (): void {
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->andReturnUsing(function (LtcsBillingStatement $actual): LtcsBillingStatement {
                    $expected = $this->statement->copy([
                        'status' => LtcsBillingStatus::fixed(),
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expected, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id,
                    LtcsBillingStatus::fixed(),
                    function (LtcsBilling $x): void {
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
                ->with(
                    '介護保険サービス：明細書が更新されました',
                    ['id' => $this->statement->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id,
                    LtcsBillingStatus::fixed(),
                    function (LtcsBilling $x): void {
                    }
                );
        });
    }
}
