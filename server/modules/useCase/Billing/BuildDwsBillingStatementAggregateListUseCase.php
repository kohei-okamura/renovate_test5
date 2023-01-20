<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書：集計一覧組み立てユースケース.
 */
interface BuildDwsBillingStatementAggregateListUseCase
{
    /**
     * 障害福祉サービス：明細書：集計の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Contract\Contract $contract
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\User\UserDwsSubsidy[]&\ScalikePHP\Option $userSubsidyOption
     * @param \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq $elements
     * @param int[]&\ScalikePHP\Option $coordinatedCopayOption 上限管理結果額
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $baseStatementOption 更新対象の明細
     * @return \Domain\Billing\DwsBillingStatementAggregate[]&\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Office $office,
        Carbon $providedIn,
        Contract $contract,
        DwsCertification $certification,
        Option $userSubsidyOption,
        Seq $elements,
        Option $coordinatedCopayOption,
        Option $baseStatementOption
    ): Seq;
}
