<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\GetDwsBillingCopayCoordinationInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateDwsBillingStatementCopayCoordinationUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusInteractor} Test.
 */
final class UpdateDwsBillingCopayCoordinationStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use GetDwsBillingCopayCoordinationInfoUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingCopayCoordinationUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UpdateDwsBillingStatementCopayCoordinationUseCaseMixin;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingStatement $billingStatement;
    private DwsBillingCopayCoordination $billingCopayCoordination;
    private array $infoArray;

    private UpdateDwsBillingCopayCoordinationStatusInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (UpdateDwsBillingCopayCoordinationStatusInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->billingCopayCoordination = $self->examples->dwsBillingCopayCoordinations[2];
            $self->billingStatement = $self->examples->dwsBillingStatements[0]->copy([
                'dwsBillingBundleId' => $self->billingBundle->id,
                'user' => $self->billingCopayCoordination->user,
            ]);

            $self->infoArray = ['response-able' => true, 'billing' => DwsBilling::create()];

            $self->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingCopayCoordination))
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('store')
                ->andReturn($self->billingCopayCoordination)
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getDwsBillingCopayCoordinationInfoUseCase
                ->allows('handle')
                ->andReturn($self->infoArray)
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->billingStatement),
                    Pagination::create()
                ))
                ->byDefault();
            $self->updateDwsBillingStatementCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingCopayCoordinationStatusInteractor::class);
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
                    $this->billingBundle->id,
                    $this->billingCopayCoordination->id,
                    DwsBillingStatus::ready(),
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupDwsBillingCopayCoordinationUseCase', function (): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingCopayCoordination->id
                )
                ->andReturn(Seq::from($this->billingCopayCoordination));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingCopayCoordination->id,
                    DwsBillingStatus::fixed(),
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingCopayCoordination->id,
                            DwsBillingStatus::fixed(),
                        );
                }
            );
        });
        $this->should(
            'use UpdateDwsBillingStatementCopayCoordinationUseCase',
            function (DwsBillingStatus $status) {
                $values = $status === DwsBillingStatus::fixed()
                    ? Option::some([
                        'result' => $this->billingCopayCoordination->result,
                        'amount' => $this->billingCopayCoordination->items[0]->subtotal->coordinatedCopay,
                    ])
                    : Option::none();

                $this->updateDwsBillingStatementCopayCoordinationUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingStatement->id,
                        equalTo($values)
                    )
                    ->andReturn([]);

                $this->interactor
                    ->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingCopayCoordination->id,
                        $status,
                    );
            },
            ['examples' => [
                'when updated status is ready' => [DwsBillingStatus::ready()],
                'when updated status is fixed' => [DwsBillingStatus::fixed()],
            ]]
        );
        $this->should('use DwsBillingCopayCoordinationRepository for updating entity', function (): void {
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBillingCopayCoordination $actual): DwsBillingCopayCoordination {
                    $expected = $this->billingCopayCoordination->copy([
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
                    $this->billingCopayCoordination->id,
                    DwsBillingStatus::fixed(),
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
                    '利用者負担上限額管理結果票が更新されました',
                    ['id' => $this->billingCopayCoordination->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingCopayCoordination->id,
                    DwsBillingStatus::fixed(),
                );
        });
    }
}
