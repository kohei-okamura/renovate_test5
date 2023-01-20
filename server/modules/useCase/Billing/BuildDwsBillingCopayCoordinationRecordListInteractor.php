<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Context\Context;
use Domain\Exchange\DwsBillingCopayCoordinationItemRecord;
use Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Generator;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス:利用者負担上限額管理結果票レコード組み立てユースケース実装
 */
final class BuildDwsBillingCopayCoordinationRecordListInteractor implements BuildDwsBillingCopayCoordinationRecordListUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListInteractor} constructor.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationFinder $copayCoordinationFinder
     */
    public function __construct(
        private readonly DwsBillingCopayCoordinationFinder $copayCoordinationFinder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array
    {
        $records = $this->generateDataRecords($billing, $bundles);
        return [
            DwsControlRecord::forCopayCoordination($billing, $records->size()),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * データレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return \Domain\Exchange\ExchangeRecord[]&\ScalikePHP\Seq
     */
    private function generateDataRecords(DwsBilling $billing, Seq $bundles): Seq
    {
        $copayCoordinationMap = $this->findCopayCoordinations($billing->id);
        return $bundles->flatMap(fn (DwsBillingBundle $bundle): iterable => $this->generateCopayCoordinationRecord(
            $bundle,
            $copayCoordinationMap->get($bundle->id)->toSeq()->flatten()
        ));
    }

    /**
     * 障害福祉サービス：利用者負担上限額管理結果票の一覧を取得する.
     *
     * @param int $billingId
     * @return \Domain\Billing\DwsBillingCopayCoordination[][]&\ScalikePHP\Map&\ScalikePHP\Seq[]
     */
    private function findCopayCoordinations(int $billingId): Map
    {
        return $this->copayCoordinationFinder
            ->find(['dwsBillingId' => $billingId], ['all' => true, 'sortBy' => 'id'])
            ->list
            ->groupBy(fn (DwsBillingCopayCoordination $x): int => $x->dwsBillingBundleId);
    }

    /**
     * 利用者負担上限額管理結果票レコードを生成する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Seq $copayCoordinations
     * @return iterable
     */
    private function generateCopayCoordinationRecord(DwsBillingBundle $bundle, Seq $copayCoordinations): iterable
    {
        return $copayCoordinations->flatMap(function (DwsBillingCopayCoordination $x) use ($bundle): Generator {
            yield DwsBillingCopayCoordinationSummaryRecord::from($bundle, $x);
            yield from DwsBillingCopayCoordinationItemRecord::from($bundle, $x);
        });
    }
}
