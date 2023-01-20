<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Closure;
use Domain\DwsCertification\CopayCoordinationType;
use Lib\Exceptions\LogicException;

/**
 * Support functions for {@link \Domain\Billing\DwsBillingStatementCopayCoordinationStatus}.
 *
 * @mixin \Domain\Billing\DwsBillingStatementCopayCoordinationStatus
 */
trait DwsBillingStatementCopayCoordinationStatusSupport
{
    /**
     * {@link \Domain\DwsCertification\CopayCoordinationType} に対応した {@link \Domain\Billing\DwsBillingStatementCopayCoordinationStatus} を返す.
     *
     * @param \Domain\DwsCertification\CopayCoordinationType $type
     * @return \Domain\Billing\DwsBillingStatementCopayCoordinationStatus
     */
    public static function fromCopayCoordinationType(CopayCoordinationType $type): self
    {
        return match ($type) {
            CopayCoordinationType::none(), CopayCoordinationType::unknown() => self::unapplicable(),
            CopayCoordinationType::internal() => self::uncreated(),
            CopayCoordinationType::external() => self::unfilled(),
            default => throw new LogicException('Unexpected CopayCoordinationType value'),
        };
    }

    /**
     * 上限管理が完了しているかどうかを返す.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        // 不要は作成する必要がないので、完了として扱う
        return $this === DwsBillingStatementCopayCoordinationStatus::unapplicable()
            || $this === DwsBillingStatementCopayCoordinationStatus::unclaimable()
            || $this === DwsBillingStatementCopayCoordinationStatus::fulfilled();
    }

    /**
     * 他社事業所が上限管理を行っているかどうかの判定を行う.
     *
     * @param \Closure $onFulfilled
     * @return bool
     */
    public function isSelfOffice(Closure $onFulfilled): bool
    {
        return match ($this) {
            // 上限管理をしていない
            DwsBillingStatementCopayCoordinationStatus::unapplicable(),
            // サービス提供なし
            DwsBillingStatementCopayCoordinationStatus::unclaimable(),
            // 未作成 ＝　自事業所で上限管理をしている
            DwsBillingStatementCopayCoordinationStatus::uncreated(),
            // 入力中 ＝ 利用者負担上限額管理結果票を作成している ＝ 自事業所で上限管理をしている
            DwsBillingStatementCopayCoordinationStatus::checking() => false,
            // 未入力 ＝ 他事業所で上限管理をしている
            DwsBillingStatementCopayCoordinationStatus::unfilled() => true,
            // 入力済 ＝ 判定できないので、処理を委譲する
            DwsBillingStatementCopayCoordinationStatus::fulfilled() => $onFulfilled(),
        };
    }
}
