<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfirmDwsBillingStatusUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\GetDwsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\EditDwsBillingStatementStatusInteractor;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatementStatusInteractor} Test.
 */
final class EditDwsBillingStatementStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ConfirmDwsBillingStatusUseCaseMixin;
    use ContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use GetDwsBillingStatementInfoUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingStatement $billingStatement;
    private array $info;

    private EditDwsBillingStatementStatusInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (EditDwsBillingStatementStatusInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->billingStatement = $self->examples->dwsBillingStatements[2];
            $self->info = [
                'billing' => $self->billing,
                'bundles' => $self->billingBundle,
                'copayCoordinations' => [$self->examples->dwsBillingCopayCoordinations[0]],
                'reports' => [$self->examples->dwsBillingServiceReports[0]],
                'statements' => [$self->billingStatement],
            ];

            $self->confirmDwsBillingStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatement))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->billingStatement)
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getDwsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->info)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditDwsBillingStatementStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return response-able array', function (): void {
            $expected = $this->info;

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    DwsBillingStatus::ready()
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupDwsBillingStatementUseCase', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id
                )
                ->andReturn(Seq::from($this->billingStatement));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    DwsBillingStatus::fixed()
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingStatement->id,
                            DwsBillingStatus::fixed()
                        );
                }
            );
        });
        $this->should('use DwsBillingStatementRepository for updating entity', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBillingStatement $actual): DwsBillingStatement {
                    $expected = $this->billingStatement->copy([
                        'status' => DwsBillingStatus::fixed(),
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expected, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    DwsBillingStatus::fixed()
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
                    ['id' => $this->billingStatement->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    DwsBillingStatus::fixed()
                );
        });
        $this->should('use ConfirmDwsBillingStatusUseCase', function (): void {
            $this->confirmDwsBillingStatusUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->info['billing']))
                ->andReturn();

            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    DwsBillingStatus::fixed()
                );
        });
    }
}
