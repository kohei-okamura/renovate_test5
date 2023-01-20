<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Billing\DwsBillingServiceReportProviderType;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Support functions for {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType}.
 *
 * @mixin \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType
 */
trait DwsHomeHelpServiceProviderTypeSupport
{
    /**
     * 居宅介護：提供者区分からサービス提供実績記録票：ヘルパー資格を導出する.
     *
     * @return \Domain\Billing\DwsBillingServiceReportProviderType
     */
    public function toDwsBillingServiceReportProviderType(): DwsBillingServiceReportProviderType
    {
        switch ($this) {
            case self::beginner():
                return DwsBillingServiceReportProviderType::beginner();
            case self::careWorkerForPwsd():
                return DwsBillingServiceReportProviderType::careWorkerForPwsd();
            case self::none():
                return DwsBillingServiceReportProviderType::novice();
            default:
                // @codeCoverageIgnoreStart
                // 追加された場合に検知する
                throw new InvalidArgumentException("DwsHomeHelpServiceProviderType to DwsBillingServiceReportProviderType({$this->value()}) is not found");
                // @codeCoverageIgnoreEnd
        }
    }
}
