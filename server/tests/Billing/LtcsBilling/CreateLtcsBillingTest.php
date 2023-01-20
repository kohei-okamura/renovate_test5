<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsBilling;

use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingFinder;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Contract\ContractRepository;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Tests\Billing\Test;

/**
 * POST /ltcs-billings に関するテストの基底クラス.
 */
abstract class CreateLtcsBillingTest extends Test
{
    /**
     * 予実リポジトリ取得.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportRepository
     */
    protected function getProvisionReportRepository(): LtcsProvisionReportRepository
    {
        return app(LtcsProvisionReportRepository::class);
    }

    /**
     * 請求Finder取得.
     *
     * @return \Domain\Billing\LtcsBillingFinder
     */
    protected function getBillingFinder(): LtcsBillingFinder
    {
        return app(LtcsBillingFinder::class);
    }

    /**
     * 請求単位Finder取得.
     *
     * @return \Domain\Billing\LtcsBillingBundleRepository
     */
    protected function getBundleRepository(): LtcsBillingBundleRepository
    {
        return app(LtcsBillingBundleRepository::class);
    }

    /**
     * 請求書Repository取得.
     *
     * @return \Domain\Billing\LtcsBillingInvoiceRepository
     */
    protected function getInvoiceRepository(): LtcsBillingInvoiceRepository
    {
        return app(LtcsBillingInvoiceRepository::class);
    }

    /**
     * 請求明細書Repository取得.
     *
     * @return \Domain\Billing\LtcsBillingStatementRepository
     */
    protected function getStatementRepository(): LtcsBillingStatementRepository
    {
        return app(LtcsBillingStatementRepository::class);
    }

    /**
     * 契約Repository取得.
     *
     * @return \Domain\Contract\ContractRepository
     */
    protected function getContractRepository(): ContractRepository
    {
        return app(ContractRepository::class);
    }
}
