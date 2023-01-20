<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Infrastructure\Model;
use Infrastructure\Shift\ServiceOptionProvider;

/**
 * サービスオプション（勤務シフト・勤務実績） Eloquent モデル.
 *
 * @property int $dws_provision_report_item_plan_id 障害福祉サービス：予実：要素：実績ID
 */
final class DwsProvisionReportItemPlanServiceOption extends Model implements ServiceOptionProvider
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_provision_report_item_plan_service_option';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'dws_provision_report_item_plan_id',
        'service_option',
    ];
}
