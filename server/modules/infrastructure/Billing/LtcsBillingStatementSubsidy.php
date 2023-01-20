<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementSubsidy as DomainBillingStatementSubsidy;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\User\CastsDefrayerCategory;

/**
 * 介護保険サービス：明細書：公費請求内容 Eloquent モデル.
 *
 * @property int $id 公費請求内容 ID
 * @property int $statement_id 明細書 ID
 * @property-read string $defrayer_number 負担者番号
 * @property-read string $recipient_number 受給者番号
 * @property-read null|int $benefit_rate 給付率
 * @property-read int $total_score サービス単位数
 * @property-read int $claim_amount 請求額
 * @property-read int $copay_amount 利用者負担額
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDefrayerNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereRecipientNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBenefitRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereClaimAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCopayAmount($value)
 */
final class LtcsBillingStatementSubsidy extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_subsidy';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'defrayer_category',
        'defrayer_number',
        'recipient_number',
        'benefit_rate',
        'total_score',
        'claim_amount',
        'copay_amount',
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
        'statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    protected $casts = [
        'defrayer_category' => CastsDefrayerCategory::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementSubsidy $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingStatementSubsidy $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingStatementSubsidy
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingStatementSubsidy::fromAssoc($attrs);
    }
}
