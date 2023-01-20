<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\DwsCertification\FindDwsCertificationUseCase;
use UseCase\Office\GetOfficeListUseCase;
use UseCase\User\EnsureUserUseCase;

/**
 * 利用者負担上限額管理結果票更新ユースケース実装.
 */
class EditDwsBillingCopayCoordinationInteractor implements EditDwsBillingCopayCoordinationUseCase
{
    use Logging;

    private EnsureDwsBillingBundleUseCase $ensureBundleUseCase;
    private EnsureUserUseCase $ensureUserUseCase;
    private FindDwsCertificationUseCase $findCertificationUseCase;
    private GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase;
    private GetOfficeListUseCase $getOfficeListUseCase;
    private EditDwsBillingStatementUseCase $editDwsBillingStatementUseCase;
    private DwsBillingStatementFinder $statementFinder;
    private DwsBillingCopayCoordinationRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureBundleUseCase
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \UseCase\DwsCertification\FindDwsCertificationUseCase $findCertificationUseCase
     * @param \UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase
     * @param \UseCase\Office\GetOfficeListUseCase $getOfficeListUseCase
     * @param \Domain\Billing\DwsBillingStatementFinder $statementFinder
     * @param \Domain\Billing\DwsBillingCopayCoordinationRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        EnsureDwsBillingBundleUseCase $ensureBundleUseCase,
        EnsureUserUseCase $ensureUserUseCase,
        FindDwsCertificationUseCase $findCertificationUseCase,
        GetDwsBillingCopayCoordinationInfoUseCase $getInfoUseCase,
        GetOfficeListUseCase $getOfficeListUseCase,
        DwsBillingStatementFinder $statementFinder,
        DwsBillingCopayCoordinationRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->ensureBundleUseCase = $ensureBundleUseCase;
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->findCertificationUseCase = $findCertificationUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
        $this->getOfficeListUseCase = $getOfficeListUseCase;
        $this->statementFinder = $statementFinder;
        $this->repository = $repository;
        $this->transaction = $factory->factory($this->repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $copayCoordinationId,
        int $userId,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        iterable $items
    ): array {
        $this->ensureBundle($context, $dwsBillingId, $dwsBillingBundleId);

        $this->ensureUser($context, $userId);

        $officeMap = $this->getOfficesMap(
            $context,
            ...Seq::fromArray($items)->map(fn (array $x): int => $x['officeId'])->toArray()
        );

        /** @var $entity */
        $entity = $this->lookupEntity($copayCoordinationId, $userId);
        $x = $this->transaction->run(function () use (
            $entity,
            $officeMap,
            $result,
            $exchangeAim,
            $items
        ): DwsBillingCopayCoordination {
            $copayCoordination = $this->buildUpdatedEntity($entity, $officeMap, $result, $exchangeAim, $items);
            return $this->repository->store($copayCoordination);
        });

        $this->logger()->info(
            '利用者負担上限額管理結果票が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getInfoUseCase->handle(
            $context,
            $dwsBillingId,
            $dwsBillingBundleId,
            $copayCoordinationId
        );
    }

    /**
     * 請求・請求単位の保証.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     */
    private function ensureBundle(Context $context, int $billingId, int $bundleId): void
    {
        $this->ensureBundleUseCase->handle($context, Permission::updateBillings(), $billingId, $bundleId);
    }

    /**
     * 利用者を保証する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     */
    private function ensureUser(Context $context, int $userId): void
    {
        $this->ensureUserUseCase
            ->handle($context, Permission::updateBillings(), $userId);
    }

    /**
     * 上限管理事業所の事業所情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$officeIds
     * @return \Domain\Office\Office[]|\ScalikePHP\Map
     */
    private function getOfficesMap(Context $context, int ...$officeIds): Map
    {
        return $this->getOfficeListUseCase
            ->handle($context, ...$officeIds)
            ->toMap('id');
    }

    /**
     * Entityを取得する.
     *
     * @param int $copayCoordinationId
     * @param int $userId
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function lookupEntity(int $copayCoordinationId, int $userId): DwsBillingCopayCoordination
    {
        return $this->repository->lookup($copayCoordinationId)
            ->filter(fn (DwsBillingCopayCoordination $x): bool => $x->user->userId === $userId)
            ->headOption()
            ->getOrElse(function () use ($copayCoordinationId): void {
                throw new NotFoundException("CopayCoordination({$copayCoordinationId}) not found");
            });
    }

    /**
     * 更新用Entity組み立て.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination $entity
     * @param \ScalikePHP\Map $officeMap
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $exchangeAim
     * @param iterable $items
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function buildUpdatedEntity(
        DwsBillingCopayCoordination $entity,
        Map $officeMap,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        iterable $items
    ): DwsBillingCopayCoordination {
        $f = call_user_func(function () use ($officeMap, $items): iterable {
            foreach ($items as $item) {
                $office = $officeMap->getOrElse($item['officeId'], function () use ($item): void {
                    throw new NotFoundException("Office({$item['officeId']}) not fund.");
                });
                yield DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => $item['itemNumber'],
                    'office' => DwsBillingOffice::from($office),
                    'subtotal' => $item['subtotal'],
                ]);
            }
        });
        $itemSeq = Seq::fromTraversable($f);
        return $entity->copy([
            'result' => $result,
            'exchangeAim' => $exchangeAim,
            'items' => $itemSeq->toArray(),
            'total' => $itemSeq->fold(
                DwsBillingCopayCoordinationPayment::create([
                    'fee' => 0,
                    'copay' => 0,
                    'coordinatedCopay' => 0,
                ]),
                fn (DwsBillingCopayCoordinationPayment $s, DwsBillingCopayCoordinationItem $item): DwsBillingCopayCoordinationPayment => $s->copy([
                    'fee' => $s->fee + $item->subtotal->fee,
                    'copay' => $s->copay + $item->subtotal->copay,
                    'coordinatedCopay' => $s->coordinatedCopay + $item->subtotal->coordinatedCopay,
                ])
            ),
            'updatedAt' => Carbon::now(),
        ]);
    }
}
