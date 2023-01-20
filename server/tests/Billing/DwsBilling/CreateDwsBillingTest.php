<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\DwsBilling;

use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingFinder;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Tests\Billing\Test;

/**
 * 障害福祉サービス 請求生成テスト共通処理.
 */
abstract class CreateDwsBillingTest extends Test
{
    /**
     * BillingFinderを取得する.
     *
     * @return \Domain\Billing\DwsBillingFinder
     */
    protected function getBillingFinder(): DwsBillingFinder
    {
        return app(DwsBillingFinder::class);
    }

    /**
     * Bundle Repository を取得する.
     *
     * @return \Domain\Billing\DwsBillingBundleRepository
     */
    protected function getBundleRepository(): DwsBillingBundleRepository
    {
        return app(DwsBillingBundleRepository::class);
    }

    /**
     * Invoice Repository を取得する.
     *
     * @return \Domain\Billing\DwsBillingInvoiceRepository
     */
    protected function getInvoiceRepository(): DwsBillingInvoiceRepository
    {
        return app(DwsBillingInvoiceRepository::class);
    }

    /**
     * Statement Repository を取得する.
     *
     * @return \Domain\Billing\DwsBillingStatementRepository
     */
    protected function getStatementRepository(): DwsBillingStatementRepository
    {
        return app(DwsBillingStatementRepository::class);
    }

    /**
     * 障害福祉サービス予実 Repository を取得する.
     *
     * @return \Domain\ProvisionReport\DwsProvisionReportRepository
     */
    protected function getProvisionReportRepository(): DwsProvisionReportRepository
    {
        return app(DwsProvisionReportRepository::class);
    }

    /**
     * 実績記録票Repository取得.
     *
     * @return \Domain\Billing\DwsBillingServiceReportRepository
     */
    protected function getServiceReportRepository(): DwsBillingServiceReportRepository
    {
        return app(DwsBillingServiceReportRepository::class);
    }
}
