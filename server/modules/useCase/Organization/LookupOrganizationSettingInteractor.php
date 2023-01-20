<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Context\Context;
use Domain\Organization\OrganizationSetting;
use Domain\Organization\OrganizationSettingRepository;
use Domain\Permission\Permission;
use ScalikePHP\Option;

/**
 * 事業者別設定取得ユースケース実装.
 */
final class LookupOrganizationSettingInteractor implements LookupOrganizationSettingUseCase
{
    private OrganizationSettingRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Organization\OrganizationSettingRepository $repository
     */
    public function __construct(OrganizationSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission): Option
    {
        // TODO: 純粋に取得して認可の処理だけおこなうようにして空のentityを返すのは別のユースケースにわけたい
        $x = $this->repository->lookupByOrganizationId($context->organization->id);
        return $x->isEmpty()
            ? Option::from(OrganizationSetting::create([]))
            : $x->values()
                ->flatten()
                ->headOption();
    }
}
