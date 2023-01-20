<?php
/*
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
 * 介護保険サービス：予実取得ユースケース実装.
 */
class GetLtcsProvisionReportInteractor implements GetLtcsProvisionReportUseCase
{
    private IdentifyContractUseCase $identifyContractUseCase;
    private FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->findLtcsProvisionReportUseCase = $findLtcsProvisionReportUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, Permission $permission, int $officeId, int $userId, Carbon $providedIn): Option
    {
        $this->identifyContractUseCase->handle(
            $context,
            $permission,
            $officeId,
            $userId,
            ServiceSegment::longTermCare(),
            $providedIn->endOfMonth()
        )->getOrElse(function (): void {
            throw new NotFoundException('No contracts');
        });

        return $this->findLtcsProvisionReportUseCase
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
