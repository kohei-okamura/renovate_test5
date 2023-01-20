<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Generator;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\Office\GetOfficeListUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 利用者負担上限額管理結果票登録ユースケース実装.
 */
final class CreateDwsBillingCopayCoordinationInteractor implements CreateDwsBillingCopayCoordinationUseCase
{
    use Logging;

    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationInteractor} constructor.
     *
     * @param \UseCase\Office\GetOfficeListUseCase $getOfficeListUseCase
     * @param IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupBundleUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\Billing\EditDwsBillingStatementUseCase $editDwsBillingStatementUseCase
     * @param \Domain\Billing\DwsBillingStatementFinder $statementFinder
     * @param \Domain\Billing\DwsBillingCopayCoordinationRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        private readonly GetOfficeListUseCase $getOfficeListUseCase,
        private readonly IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
        private readonly LookupDwsBillingUseCase $lookupBillingUseCase,
        private readonly LookupDwsBillingBundleUseCase $lookupBundleUseCase,
        private readonly LookupOfficeUseCase $lookupOfficeUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase,
        private readonly EditDwsBillingStatementUseCase $editDwsBillingStatementUseCase,
        private readonly DwsBillingStatementFinder $statementFinder,
        private readonly DwsBillingCopayCoordinationRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->transaction = $factory->factory($this->repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $userId,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        iterable $items
    ): array {
        $user = $this->lookupUser($context, $userId);
        $billing = $this->lookupBilling($context, $billingId);
        $bundle = $this->lookupBundle($context, $billingId, $bundleId);
        $copayCoordination = $this->transaction->run(fn (): DwsBillingCopayCoordination => $this->create(
            $context,
            $billing,
            $bundle,
            $user,
            $result,
            $exchangeAim,
            $items
        ));
        $this->logger()->info(
            '利用者負担上限額管理結果票が登録されました',
            ['id' => $copayCoordination->id] + $context->logContext()
        );
        return [
            'billing' => $billing,
            'bundle' => $bundle,
            'copayCoordination' => $copayCoordination,
        ];
    }

    /**
     * 請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @return \Domain\Billing\DwsBilling
     */
    private function lookupBilling(Context $context, int $billingId): DwsBilling
    {
        return $this->lookupBillingUseCase
            ->handle($context, Permission::createBillings(), $billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId): void {
                throw new NotFoundException("DwsBilling({$billingId}) not found");
            });
    }

    /**
     * 請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingBundle
     */
    private function lookupBundle(Context $context, int $billingId, int $bundleId): DwsBillingBundle
    {
        return $this->lookupBundleUseCase
            ->handle($context, Permission::createBillings(), $billingId, $bundleId)
            ->headOption()
            ->getOrElse(function () use ($bundleId): void {
                throw new NotFoundException("DwsBillingBundle({$bundleId}) not found");
            });
    }

    /**
     * 利用者負担上限額管理結果票を組み立ててリポジトリに格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $exchangeAim
     * @param array $items
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    private function create(
        Context $context,
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        User $user,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        array $items,
    ): DwsBillingCopayCoordination {
        $certification = $this->identifyCertification($context, $bundle, $user);
        $officeMap = $this->lookupOffices($context, $certification, $items);
        $itemSeq = Seq::from(...self::generateItems($items, $officeMap));
        $copayCoordination = DwsBillingCopayCoordination::create([
            'dwsBillingId' => $billing->id,
            'dwsBillingBundleId' => $bundle->id,
            'office' => self::buildDwsBillingOffice($certification, $officeMap),
            'user' => DwsBillingUser::from($user, $certification),
            'result' => $result,
            'exchangeAim' => $exchangeAim,
            'items' => $itemSeq->toArray(),
            'total' => self::computeTotal($itemSeq),
            'status' => DwsBillingStatus::ready(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        $this->updateStatement($context, $billing, $bundle, $user);
        return $this->repository->store($copayCoordination);
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::createBillings(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }

    /**
     * 障害福祉サービス受給者証を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyCertification(Context $context, DwsBillingBundle $bundle, User $user): DwsCertification
    {
        return $this->identifyDwsCertificationUseCase
            ->handle($context, $user->id, $bundle->providedIn)
            ->getOrElse(function () use ($user): void {
                throw new NotFoundException("DwsCertification(user={$user->id}) not found");
            });
    }

    /**
     * 事業所の一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param array $items
     * @return \Domain\Office\Office[]&\ScalikePHP\Map
     */
    private function lookupOffices(Context $context, DwsCertification $certification, array $items): Map
    {
        $ids = Seq::fromArray($items)
            ->map(fn (array $x): int => $x['officeId'])
            ->append([$certification->copayCoordination->officeId])
            ->distinct()
            ->sortBy(fn (int $id): int => $id)
            ->toArray();
        return $this->getOfficeListUseCase
            ->handle($context, ...$ids)
            ->toMap('id');
    }

    /**
     * {@link \Domain\Billing\DwsBillingCopayCoordinationItem} の一覧を生成する.
     *
     * @param array $items
     * @param \Domain\Office\Office[]&\ScalikePHP\Map $officeMap
     * @return \Domain\Billing\DwsBillingCopayCoordinationItem[]&\Generator
     */
    private static function generateItems(array $items, Map $officeMap): Generator
    {
        foreach ($items as $item) {
            $office = $officeMap->getOrElse($item['officeId'], function () use ($item): void {
                throw new NotFoundException("Office({$item['officeId']}) not fund");
            });
            yield DwsBillingCopayCoordinationItem::create([
                'itemNumber' => $item['itemNumber'],
                'office' => DwsBillingOffice::from($office),
                'subtotal' => $item['subtotal'],
            ]);
        }
    }

    /**
     * {@link \Domain\Billing\DwsBillingOffice} を組み立てる.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Office\Office[]&\ScalikePHP\Map $officeMap
     * @return \Domain\Billing\DwsBillingOffice
     */
    private static function buildDwsBillingOffice(DwsCertification $certification, Map $officeMap): DwsBillingOffice
    {
        $office = $officeMap->getOrElse(
            $certification->copayCoordination->officeId,
            function () use ($certification): void {
                throw new NotFoundException("CopayCoordinationOffice({$certification->copayCoordination->officeId}) not found");
            }
        );
        return DwsBillingOffice::from($office);
    }

    /**
     * 合計を算出する.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationItem[]&\ScalikePHP\Seq $itemSeq
     * @return \Domain\Billing\DwsBillingCopayCoordinationPayment
     */
    private static function computeTotal(Seq $itemSeq): DwsBillingCopayCoordinationPayment
    {
        return $itemSeq->fold(
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
     * 明細書を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @return void
     */
    private function updateStatement(Context $context, DwsBilling $billing, DwsBillingBundle $bundle, User $user): void
    {
        $statement = $this->statementFinder
            ->find(
                filterParams: [
                    'dwsBillingBundleId' => $bundle->id,
                    'userId' => $user->id,
                ],
                paginationParams: [
                    'all' => true,
                    'sortBy' => 'id',
                ]
            )
            ->list
            ->headOption()
            ->getOrElse(function () use ($bundle, $user): void {
                throw new NotFoundException("DwsBillingStatement({ dwsBillingBundleId: {$bundle->id}, userId: {$user->id}}) not found");
            });
        $this->editDwsBillingStatementUseCase->handle(
            $context,
            $billing->id,
            $bundle->id,
            $statement->id,
            ['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking()]
        );
    }
}
