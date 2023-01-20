<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

/**
 * サービス提供実績記録票：実績 Eloquent モデル.
 */
final class DwsBillingServiceReportResult extends DwsBillingServiceReportAggregate
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_service_report_result';

    /** {@inheritdoc} */
    protected $table = self::TABLE;
}
