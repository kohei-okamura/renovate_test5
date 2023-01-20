<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpecFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 障害福祉サービス：居宅介護：算定情報検索ユースケース実装.
 */
final class FindHomeHelpServiceCalcSpecInteractor implements FindHomeHelpServiceCalcSpecUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Office\HomeHelpServiceCalcSpecFinder $finder
     */
    public function __construct(HomeHelpServiceCalcSpecFinder $finder)
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
