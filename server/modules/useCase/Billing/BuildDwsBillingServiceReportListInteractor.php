<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\User\LookupUserUseCase;

/**
 * 障害福祉サービス：サービス提供実績記録票生成ユースケース実装.
 */
final class BuildDwsBillingServiceReportListInteractor implements BuildDwsBillingServiceReportListUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceReportListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase $buildByIdUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private BuildDwsBillingServiceReportListByIdUseCase $buildByIdUseCase,
        private LookupUserUseCase $lookupUserUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsBillingBundle $bundle,
        DwsProvisionReport $provisionReport,
        Option $previousProvisionReport
    ): Seq {
        $user = $this->lookupUser($context, $provisionReport->userId);
        return $this->buildByIdUseCase->handle(
            $context,
            $bundle->dwsBillingId,
            $bundle->id,
            $provisionReport,
            $previousProvisionReport,
            $user
        );
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::createBillings(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }
}
