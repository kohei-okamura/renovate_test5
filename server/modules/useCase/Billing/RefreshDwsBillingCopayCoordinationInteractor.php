<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 利用者負担上限額管理結果票リフレッシュユースケース実装.
 */
class RefreshDwsBillingCopayCoordinationInteractor implements RefreshDwsBillingCopayCoordinationUseCase
{
    use Logging;

    private DwsBillingCopayCoordinationRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\RefreshDwsBillingCopayCoordinationInteractor} constructor.
     * @param DwsBillingCopayCoordinationRepository $repository
     * @param TransactionManagerFactory $factory
     */
    public function __construct(
        DwsBillingCopayCoordinationRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsBillingStatement $statement,
        Option $copayCoordination,
        DwsCertification $dwsCertification,
        Office $office
    ): void {
        if ($this->isSelfCoordination($dwsCertification, $office)) {
            $copayCoordination->each(function (DwsBillingCopayCoordination $x) use ($dwsCertification, $context, $statement): void {
                $updatedCopayCoordination = $this->updateDwsBillingCopayCoordination($x, $statement, $dwsCertification);
                $this->logger()->info(
                    '利用者負担上限額管理結果票が更新されました',
                    ['id' => $updatedCopayCoordination->id] + $context->logContext()
                );
            });
        } else {
            $copayCoordination->each(function (DwsBillingCopayCoordination $x) use ($context): void {
                $this->transaction->run(fn () => $this->repository->remove($x));
                $this->logger()->info(
                    '利用者負担上限額管理結果票が削除されました',
                    ['id' => $x->id] + $context->logContext()
                );
            });
        }
    }

    /**
     * 利用者負担上限額管理結果票を更新する.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination $copayCoordination
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param \Domain\DwsCertification\DwsCertification $dwsCertification
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function updateDwsBillingCopayCoordination(
        DwsBillingCopayCoordination $copayCoordination,
        DwsBillingStatement $statement,
        DwsCertification $dwsCertification
    ): DwsBillingCopayCoordination {
        $items = $this->buildItems($copayCoordination, $statement);
        $total = $this->buildTotal($items);
        $user = $copayCoordination->user->copy([
            'copayLimit' => $dwsCertification->copayLimit,
        ]);
        return $this->transaction->run(fn (): DwsBillingCopayCoordination => $this->repository->store($copayCoordination->copy([
            'total' => $total,
            'items' => $items,
            'user' => $user,
            'status' => DwsBillingStatus::ready(),
        ])));
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

    /**
     * 上限管理が自事業所であるか判定する.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Office\Office $office
     * @return bool
     */
    private function isSelfCoordination(DwsCertification $certification, Office $office): bool
    {
        return $certification->copayCoordination->copayCoordinationType === CopayCoordinationType::internal()
            && $certification->copayCoordination->officeId === $office->id;
    }
}
