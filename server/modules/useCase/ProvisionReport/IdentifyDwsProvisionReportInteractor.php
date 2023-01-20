<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Option;

class IdentifyDwsProvisionReportInteractor implements IdentifyDwsProvisionReportUseCase
{
    /**
     * constructor.
     *
     * @param \UseCase\ProvisionReport\FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
     */
    public function __construct(
        private readonly FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        Carbon $providedIn
    ): Option {
        return $this->findDwsProvisionReportUseCase
            ->handle(
                $context,
                $permission,
                [
                    'officeId' => $officeId,
                    'userId' => $userId,
                    'providedIn' => Carbon::parse($providedIn),
                ],
                ['all' => true],
            )
            ->list
            ->headOption();
    }
}
