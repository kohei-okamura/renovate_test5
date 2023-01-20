<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingInvoice as DomainInvoice;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\User\CastsDefrayerCategory;

/**
 * 介護保険サービス：請求書 Eloquent モデル.
 *
 * @property int $id 請求書 ID
 * @property int $billing_id 請求 ID
 * @property int $bundle_id 請求単位 ID
 * @property bool $is_subsidy 公費フラグ
 * @property int $statement_count サービス費用：件数
 * @property int $total_score サービス費用：単位数
 * @property int $total_fee サービス費用：費用合計
 * @property int $insurance_amount サービス費用：保険請求額
 * @property int $subsidy_amount サービス費用：公費請求額
 * @property int $copay_amount サービス費用：利用者負担
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBillingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereIsSubsidy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStatementCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereInsuranceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereSubsidyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCopayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsBillingInvoice extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_invoice';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'billing_id',
        'bundle_id',
        'is_subsidy',
        'defrayer_category',
        'statement_count',
        'total_score',
        'total_fee',
        'insurance_amount',
        'subsidy_amount',
        'copay_amount',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'is_subsidy' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'defrayer_category' => CastsDefrayerCategory::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingInvoice $domain
     * @return static
     */
    public static function fromDomain(DomainInvoice $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainInvoice
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainInvoice::fromAssoc($attrs);
    }
}
