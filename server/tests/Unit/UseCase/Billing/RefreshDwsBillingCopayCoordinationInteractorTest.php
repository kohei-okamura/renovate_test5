<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\RefreshDwsBillingCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingCopayCoordinationInteractor} のテスト.
 */
final class RefreshDwsBillingCopayCoordinationInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBillingStatement $statement;
    private Option $copayCoordination;
    private DwsCertification $dwsCertification;
    private Office $office;
    private RefreshDwsBillingCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingCopayCoordinationRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingCopayCoordinations[0])
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('remove')
                ->andReturnNull()
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->statement = $self->examples->dwsBillingStatements[0];
            $self->copayCoordination = Option::some($self->examples->dwsBillingCopayCoordinations[0]);
            $self->dwsCertification = $self->examples->dwsCertifications[0]->copy([
                'copayCoordination' => $self->examples->dwsCertifications[0]->copayCoordination->copy([
                    'copayCoordinationType' => CopayCoordinationType::internal(),
                    'officeId' => $self->examples->offices[0]->id,
                ]),
            ]);
            $self->office = $self->examples->offices[0];
            $self->interactor = app(RefreshDwsBillingCopayCoordinationInteractor::class);
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
                ->andReturn($this->copayCoordination->get());
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->never();
            $this->dwsBillingCopayCoordinationRepository
                ->expects('remove')
                ->never();

            $this->interactor->handle(
                $this->context,
                $this->statement,
                $this->copayCoordination,
                $this->dwsCertification,
                $this->office
            );
        });
        $this->should('use store on DwsBillingCopayCoordinationRepository when copayCoordination is self', function (): void {
            $items = $this->buildItems($this->copayCoordination->get(), $this->statement);
            $total = $this->buildTotal($items);
            $copayCoordination = $this->copayCoordination->get();
            $user = $copayCoordination->user->copy([
                'copayLimit' => $this->dwsCertification->copayLimit,
            ]);
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->with(equalTo($copayCoordination->copy([
                    'total' => $total,
                    'items' => $items,
                    'user' => $user,
                    'status' => DwsBillingStatus::ready(),
                ])))
                ->andReturn($this->examples->dwsBillingCopayCoordinations[0]);

            $this->interactor->handle(
                $this->context,
                $this->statement,
                $this->copayCoordination,
                $this->dwsCertification,
                $this->office
            );
        });
        $this->should('log using info when copayCoordination is self', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者負担上限額管理結果票が更新されました', ['id' => $this->copayCoordination->get()->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->statement,
                $this->copayCoordination,
                $this->dwsCertification,
                $this->office
            );
        });
        $this->should('use remove on DwsBillingCopayCoordinationRepository when copayCoordination is not self', function (): void {
            $dwsCertification = $this->dwsCertification->copy([
                'copayCoordination' => $this->dwsCertification->copayCoordination->copy(['officeId' => 10]),
            ]);
            $this->dwsBillingCopayCoordinationRepository
                ->expects('remove')
                ->with(equalTo($this->copayCoordination->get()))
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->statement,
                $this->copayCoordination,
                $dwsCertification,
                $this->office
            );
        });
        $this->should('log using info when copayCoordination is not self', function (): void {
            $dwsCertification = $this->dwsCertification->copy([
                'copayCoordination' => $this->dwsCertification->copayCoordination->copy(['officeId' => 10]),
            ]);
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者負担上限額管理結果票が削除されました', ['id' => $this->copayCoordination->get()->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->statement,
                $this->copayCoordination,
                $dwsCertification,
                $this->office
            );
        });
    }

    /**
     * 利用者負担上限額管理結果票 明細を組み立てる.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination $copayCoordination
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return array|\Domain\Billing\DwsBillingCopayCoordinationItem[]
     */
    private function buildItems(DwsBillingCopayCoordination $copayCoordination, DwsBillingStatement $statement): array
    {
        $items = Seq::from(...$copayCoordination->items);
        return [
            $items->head()->copy([
                'subtotal' => $items->head()->subtotal->copy([
                    'fee' => $statement->totalFee,
                    'copay' => $statement->totalCappedCopay,
                ]),
            ]),
            ...$items->tail(),
        ];
    }

    /**
     * 利用者負担上限額管理結果票 合計を組み立てる.
     *
     * @param array $items
     * @return \Domain\Billing\DwsBillingCopayCoordinationPayment
     */
    private function buildTotal(array $items): DwsBillingCopayCoordinationPayment
    {
        return Seq::from(...$items)->fold(
            DwsBillingCopayCoordinationPayment::create([
                'fee' => 0,
                'copay' => 0,
                'coordinatedCopay' => 0,
            ]),
            fn (
                DwsBillingCopayCoordinationPayment $s,
                DwsBillingCopayCoordinationItem $item
            ): DwsBillingCopayCoordinationPayment => $s->copy([
                'fee' => $s->fee + $item->subtotal->fee,
                'copay' => $s->copay + $item->subtotal->copay,
                'coordinatedCopay' => $s->coordinatedCopay + $item->subtotal->coordinatedCopay,
            ])
        );
    }
}
