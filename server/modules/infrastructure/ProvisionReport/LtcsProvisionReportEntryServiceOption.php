<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Infrastructure\Model;
use Infrastructure\Shift\ServiceOptionHolder;
use Infrastructure\Shift\ServiceOptionProvider;

/**
 * サービスオプション（勤務シフト・勤務実績） Eloquent モデル.
 *
 * @property int $ltcs_provision_report_entry_id 勤務実績ID
 */
final class LtcsProvisionReportEntryServiceOption extends Model implements ServiceOptionProvider
{
    use ServiceOptionHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_provision_report_entry_service_option';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'ltcs_provision_report_entry_id',
        'service_option',
    ];
}
