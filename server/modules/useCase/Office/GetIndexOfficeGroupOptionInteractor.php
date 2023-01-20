<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\OfficeGroup;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 事業所グループ選択肢一覧取得ユースケース実装.
 */
class GetIndexOfficeGroupOptionInteractor implements GetIndexOfficeGroupOptionUseCase
{
    private FindOfficeGroupUseCase $findOfficeGroupUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\FindOfficeGroupUseCase $findOfficeGroupUseCase
     */
    public function __construct(FindOfficeGroupUseCase $findOfficeGroupUseCase)
    {
        $this->findOfficeGroupUseCase = $findOfficeGroupUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission): Seq
    {
        return $this->findOfficeGroupUseCase->handle(
            $context,
            [],
            ['all' => true]
        )
            ->list
            ->map(fn (OfficeGroup $officeGroup): array => [
                'text' => $officeGroup->name,
                'value' => $officeGroup->id,
            ]);
    }
}
