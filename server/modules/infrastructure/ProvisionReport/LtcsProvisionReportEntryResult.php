<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\Common\Carbon;
use Infrastructure\Model;

/**
 * 介護保険サービス：予実：実績年月日 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：予実：実績年月日ID
 * @property int $ltcs_provision_report_entry_id 介護保険サービス：予実：サービス情報ID
 * @property int $sort_order 並び順
 * @property \Domain\Common\Carbon $date 年月日
 */
final class LtcsProvisionReportEntryResult extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_provision_report_entry_result';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_provision_report_entry_id',
        'sort_order',
        'date',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Common\Carbon $domain
     * @param array $additional
     * @return \Infrastructure\ProvisionReport\LtcsProvisionReportEntryResult
     */
    public static function fromDomain(Carbon $domain, array $additional): self
    {
        $values = ['date' => $domain];
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }
}
