<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

/**
 * Support functions for {@link \Domain\DwsCertification\DwsCertificationServiceType}.
 *
 * @mixin \Domain\DwsCertification\DwsCertificationServiceType
 */
trait DwsCertificationServiceTypeSupport
{
    /**
     * サービス種別が居宅介護であるか判定する.
     */
    public function isHomeHelpService(): bool
    {
        return $this === DwsCertificationServiceType::physicalCare()
            || $this === DwsCertificationServiceType::housework()
            || $this === DwsCertificationServiceType::accompanyWithPhysicalCare()
            || $this === DwsCertificationServiceType::accompany();
    }

    /**
     * サービス種別が重度訪問介護であるか判定する.
     */
    public function isVisitingCareForPwsd(): bool
    {
        return $this === DwsCertificationServiceType::visitingCareForPwsd1()
            || $this === DwsCertificationServiceType::visitingCareForPwsd2()
            || $this === DwsCertificationServiceType::visitingCareForPwsd3();
    }
}
