<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

/**
 * Support functions for {@link \Domain\Project\DwsProjectServiceCategory}.
 *
 * @mixin \Domain\Project\DwsProjectServiceCategory
 */
trait DwsProjectServiceCategorySupport
{
    /**
     * サービス区分が居宅かどうかを判定する.
     *
     * @return bool
     */
    public function isHomeHelpService(): bool
    {
        return in_array($this, [
            self::physicalCare(),
            self::housework(),
            self::accompanyWithPhysicalCare(),
            self::accompany(),
        ], true);
    }
}
