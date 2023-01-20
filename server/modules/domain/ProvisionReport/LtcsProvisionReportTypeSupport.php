<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\LtcsInsCard\LtcsLevel;
use Lib\Exceptions\LogicException;

/**
 * Support functions for {@link \Domain\ProvisionReport\LtcsProvisionReportType}.
 *
 * @mixin \Domain\ProvisionReport\LtcsProvisionReportType
 */
trait LtcsProvisionReportTypeSupport
{
    /**
     * 要介護度に応じた予実区分を返す.
     *
     * @param \Domain\LtcsInsCard\LtcsLevel $ltcsLevel
     * @return \Domain\ProvisionReport\LtcsProvisionReportType
     */
    public static function fromLtcsLevel(LtcsLevel $ltcsLevel): LtcsProvisionReportType
    {
        return match ($ltcsLevel) {
            LtcsLevel::target(),
            LtcsLevel::supportLevel1(),
            LtcsLevel::supportLevel2() => self::comprehensiveService(),
            LtcsLevel::careLevel1(),
            LtcsLevel::careLevel2(),
            LtcsLevel::careLevel3(),
            LtcsLevel::careLevel4(),
            LtcsLevel::careLevel5() => self::homeVisitLongTermCare(),
            default => throw new LogicException('Unexpected LtcsLevel value'),
        };
    }
}
