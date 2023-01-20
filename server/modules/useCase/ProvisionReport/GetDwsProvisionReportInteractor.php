<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 障害福祉サービス：予実取得ユースケース実装.
 */
final class GetDwsProvisionReportInteractor implements GetDwsProvisionReportUseCase
{
    private IdentifyContractUseCase $identifyContractUseCase;
    private FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\ProvisionReport\FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->findDwsProvisionReportUseCase = $findDwsProvisionReportUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        Carbon $providedIn
    ): Option {
        $this->identifyContractUseCase->handle(
            $context,
            $permission,
            $officeId,
            $userId,
            ServiceSegment::disabilitiesWelfare(),
            $providedIn->endOfMonth()
        )->getOrElse(function (): void {
            throw new NotFoundException('No contracts');
        });

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
