<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 重複契約取得ユースケース実装.
 */
class GetOverlapContractInteractor implements GetOverlapContractUseCase
{
    private FindContractUseCase $findContractUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Contract\FindContractUseCase $findContractUseCase
     */
    public function __construct(FindContractUseCase $findContractUseCase)
    {
        $this->findContractUseCase = $findContractUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Permission $permission,
        int $userId,
        int $officeId,
        ServiceSegment $serviceSegment,
    ): Seq {
        $status = [ContractStatus::provisional(), ContractStatus::formal()];
        $filterParams = compact(
            'userId',
            'officeId',
            'serviceSegment',
            'status'
        );

        return $this->findContractUseCase
            ->handle($context, $permission, $filterParams, ['all' => true, 'sortBy' => 'id', 'desc' => true])
            ->list;
    }
}
