<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use ScalikePHP\Seq;

/**
 * スタッフ選択肢一覧取得ユースケース実装.
 */
class GetIndexStaffOptionInteractor implements GetIndexStaffOptionUseCase
{
    private FindStaffUseCase $findStaffUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\FindStaffUseCase $findStaffUseCase
     */
    public function __construct(FindStaffUseCase $findStaffUseCase)
    {
        $this->findStaffUseCase = $findStaffUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, array $officeIds): Seq
    {
        return $this->findStaffUseCase->handle(
            $context,
            $permission,
            ['officeIds' => $officeIds],
            ['all' => true]
        )
            ->list
            ->map(fn (Staff $staff): array => [
                'text' => $staff->name->displayName,
                'value' => $staff->id,
            ]);
    }
}
