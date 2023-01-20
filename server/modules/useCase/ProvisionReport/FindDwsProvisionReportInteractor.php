<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 障害福祉サービス：予実検索ユースケース実装.
 */
final class FindDwsProvisionReportInteractor implements FindDwsProvisionReportUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReportFinder $finder
     */
    public function __construct(DwsProvisionReportFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission);
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}
