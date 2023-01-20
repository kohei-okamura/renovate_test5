<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

/**
 * サービス提供実績記録票：予定（計画） Eloquent モデル.
 */
final class DwsBillingServiceReportPlan extends DwsBillingServiceReportAggregate
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_service_report_plan';

    /** {@inheritdoc} */
    protected $table = self::TABLE;
}
