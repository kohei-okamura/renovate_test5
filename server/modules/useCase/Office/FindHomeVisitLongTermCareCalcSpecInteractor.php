<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpecFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 介護保険サービス：訪問介護：算定情報検索ユースケース実装.
 */
final class FindHomeVisitLongTermCareCalcSpecInteractor implements FindHomeVisitLongTermCareCalcSpecUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecFinder $finder
     */
    public function __construct(HomeVisitLongTermCareCalcSpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission) + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        // 下記のような並び順にしたいので default 値は設定しない（Finder で指定している）
        // 1. 適用期間開始日の降順
        // 2. 適用期間終了日の降順
        // 3. 登録日時（または主キー）の降順
        return '';
    }
}
