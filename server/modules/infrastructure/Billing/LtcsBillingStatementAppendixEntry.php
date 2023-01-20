<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry as DomainAppendixEntry;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：明細書：サービス提供票別表 Eloquent モデル.
 *
 * @property int $id サービス情報 ID
 * @property int $statement_appendix_id サービス提供票別表 ID
 * @property int $entry_type サービス情報区分
 * @property int $sort_order 表示順
 * @property string $office_name 事業所名
 * @property string $office_code 事業所番号
 * @property string $service_name サービス内容/種類
 * @property string $service_code サービスコード
 * @property int $unit_score 単位数
 * @property int $count 回数
 * @property int $whole_score 総単位数
 * @property int $max_benefit_quota_excess_score 種類支給限度基準を超える単位数
 * @property int $max_benefit_excess_score 区分支給限度基準を超える単位数
 * @property \Domain\Common\Decimal $unit_cost 単位数単価
 * @property int $benefit_rate 給付率
 * @property int $score_within_max_benefit_quota 種類支給限度基準内単位数
 * @property int $score_within_max_benefit 区分支給限度基準内単位数
 * @property int $total_fee_for_insurance_or_business 総費用額（保険/事業対象分）
 * @property int $claim_amount_for_insurance_or_business 保険/事業費請求額
 * @property int $copay_for_insurance_or_business 利用者負担（保険/事業対象分）
 * @property int $copay_whole_expense 利用者負担（全額負担分）
 */
final class LtcsBillingStatementAppendixEntry extends Model implements Domainable
{
    /** サービス情報区分：支給限度対象外 */
    public const ENTRY_TYPE_UNMANAGED = 1;

    /** サービス情報区分：支給限度対象 */
    public const ENTRY_TYPE_MANAGED = 2;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_appendix_entry';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'office_name',
        'office_code',
        'service_name',
        'service_code',
        'unit_score',
        'count',
        'whole_score',
        'max_benefit_quota_excess_score',
        'max_benefit_excess_score',
        'unit_cost',
        'benefit_rate',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'statement_appendix_id',
        'entry_type',
        'sort_order',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'unit_cost' => CastsDecimal::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry $domain
     * @param int $appendixId
     * @param int $entryType
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(
        DomainAppendixEntry $domain,
        int $appendixId,
        int $entryType,
        int $sortOrder
    ): self {
        $keys = [
            'statement_appendix_id' => $appendixId,
            'entry_type' => $entryType,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainAppendixEntry
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainAppendixEntry::fromAssoc($attrs);
    }
}
