<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * 利用者負担上限額管理結果票：明細検証ユースケース実装.
 */
final class ValidateCopayCoordinationItemInteractor implements ValidateCopayCoordinationItemUseCase
{
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;
    private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase;
    private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     */
    public function __construct(
        LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
    ) {
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
        $this->lookupDwsBillingBundleUseCase = $lookupDwsBillingBundleUseCase;
        $this->identifyDwsCertificationUseCase = $identifyDwsCertificationUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Seq $items,
        CopayCoordinationResult $result,
        int $userId,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        Permission $permission
    ): bool {
        $headItem = $items->headOption()->orNull();

        // 「利用者負担額」または「調整後利用者負担額」が不正の場合ここではエラーとしない
        if ($items->exists(
            fn (array $item) => !isset($item['subtotal']['copay'])
                || !isset($item['subtotal']['coordinatedCopay'])
                || !isset($item['officeId'])
                || !is_int($item['subtotal']['copay'])
                || !is_int($item['subtotal']['coordinatedCopay'])
        )) {
            return true;
        }

        /** @var \Domain\Billing\DwsBilling $dwsBilling */
        $dwsBilling = $this->lookupDwsBillingUseCase
            ->handle($context, $permission, $dwsBillingId)
            ->headOption()
            ->orNull();
        // 障害福祉サービス：請求が見つからない場合ここではエラーとしない
        if ($dwsBilling === null) {
            return true;
        }

        /** @var \Domain\Billing\DwsBillingBundle $dwsBillingBundle */
        $dwsBillingBundle = $this->lookupDwsBillingBundleUseCase
            ->handle($context, $permission, $dwsBillingId, $dwsBillingBundleId)
            ->headOption()
            ->orNull();
        // 障害福祉サービス：請求単位が見つからない場合ここではエラーとしない
        if ($dwsBillingBundle === null) {
            return true;
        }

        /** @var \Domain\DwsCertification\DwsCertification $dwsCertification */
        $dwsCertification = $this->identifyDwsCertificationUseCase
            ->handle($context, $userId, $dwsBillingBundle->providedIn)
            ->orNull();
        if ($dwsCertification === null) {
            return true;
        }

        if ($result === CopayCoordinationResult::appropriated()) {
            // 上限管理事業所の利用がない場合
            if ($dwsBilling->office->officeId !== $headItem['officeId']) {
                return false;
            }
            // 上限管理事業所（項番1）の利用者負担額が負担上限月額以上
            // 上限管理事業所（項番1）の管理結果後利用者負担額と負担上限月額が等しい
            // 項番2以降の管理結果後利用者負担額が0
            $copay = $headItem['subtotal']['copay'];
            $coordinatedCopay = $headItem['subtotal']['coordinatedCopay'];
            return $copay >= $dwsCertification->copayLimit
                && $coordinatedCopay === $dwsCertification->copayLimit
                && $items->takeRight($items->size() - 1)
                    ->forAll(fn (array $item): bool => $item['subtotal']['coordinatedCopay'] === 0);
        } elseif ($result === CopayCoordinationResult::notCoordinated()) {
            // 全事業所の利用者負担額の総額が負担上限月額以下
            // 全事業所の利用者負担額と管理結果後利用者負担額が等しい
            $sum = $items->map(fn (array $item): int => $item['subtotal']['copay'])->sum();
            return $sum <= $dwsCertification->copayLimit
                && $items->forAll(
                    fn (array $item) => $item['subtotal']['copay'] === $item['subtotal']['coordinatedCopay']
                );
        } elseif ($result === CopayCoordinationResult::coordinated()) {
            // 項番1の利用者負担額が負担上限月額未満
            // 項番1の利用者負担額と管理結果後利用者負担額が等しい
            // 全事業所の利用者負担額の総額が負担上限月額以上
            // 全事業所の管理結果後利用者負担額の総額が負担上限月額に等しい
            return $headItem['subtotal']['copay'] < $dwsCertification->copayLimit
                && $headItem['subtotal']['copay'] === $headItem['subtotal']['coordinatedCopay']
                && $items->map(fn (array $item): int => $item['subtotal']['copay'])
                    ->sum() >= $dwsCertification->copayLimit
                && $items->map(fn (array $item): int => $item['subtotal']['coordinatedCopay'])
                    ->sum() === $dwsCertification->copayLimit;
        } else {
            throw new LogicException("CopayCoordinationResult cannot be {$result->value()}");
        }
    }
}
