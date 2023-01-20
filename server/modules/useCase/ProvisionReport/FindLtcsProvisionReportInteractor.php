<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 介護保険サービス：予実検索ユースケース実装.
 */
final class FindLtcsProvisionReportInteractor implements FindLtcsProvisionReportUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportFinder $finder
     */
    public function __construct(LtcsProvisionReportFinder $finder)
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
