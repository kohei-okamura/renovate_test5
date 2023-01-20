<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 事業所選択肢一覧取得ユースケース.
 */
interface GetIndexOfficeOptionUseCase
{
    /**
     * 事業所選択肢を一覧取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission|\ScalikePHP\Option $permissionOption
     * @param int[]|\ScalikePHP\Option $userIdOption
     * @param \Domain\Office\Purpose[]|\ScalikePHP\Option $purposeOption
     * @param bool[]|\ScalikePHP\Option $isCommunityGeneralSupportCenter
     * @param \Domain\Office\OfficeQualification[]|\ScalikePHP\Seq $qualifications
     * @return \Domain\Office\OfficeOption[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Option $permissionOption,
        Option $userIdOption,
        Option $purposeOption,
        Option $isCommunityGeneralSupportCenter,
        Seq $qualifications
    ): Seq;
}
