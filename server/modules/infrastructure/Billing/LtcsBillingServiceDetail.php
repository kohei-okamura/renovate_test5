<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingServiceDetail as DomainServiceDetail;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\ServiceCodeDictionary\CastsLtcsNoteRequirement;
use Infrastructure\ServiceCodeDictionary\CastsLtcsServiceCodeCategory;

/**
 * 介護保険サービス：請求：サービス詳細 Eloquent モデル.
 *
 * @property int $id サービス詳細 ID
 * @property int $bundle_id 請求単位 ID
 * @property int $user_id 利用者 ID
 * @property \Domain\Billing\LtcsBillingServiceDetailDisposition $disposition 区分
 * @property \Domain\Common\Carbon $provided_on サービス提供年月日
 * @property string $service_code サービスコード
 * @property \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $service_code_category サービスコード区分
 * @property \Domain\ProvisionReport\LtcsBuildingSubtraction $building_subtraction 同一建物減算区分
 * @property \Domain\ServiceCodeDictionary\LtcsNoteRequirement $note_requirement 摘要欄記載要件
 * @property bool $is_addition 加算フラグ
 * @property bool $is_limited 支給限度額対象フラグ
 * @property int $duration_minutes 所要時間
 * @property int $unit_score 単位数
 * @property int $count 回数
 * @property int $whole_score 総サービス単位数
 * @property int $max_benefit_quota_excess_score 種類支給限度基準を超える単位数
 * @property int $max_benefit_excess_score 区分支給限度基準を超える単位数
 * @property int $total_score サービス単位数
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBillingBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDisposition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereProvidedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUnitScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereWholeScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereMaxBenefitQuotaExcessScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereMaxBenefitExcessScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 */
final class LtcsBillingServiceDetail extends Model implements Domainable
{
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_service_detail';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'user_id',
        'disposition',
        'provided_on',
        'service_code',
        'service_code_category',
        'building_subtraction',
        'note_requirement',
        'is_addition',
        'is_limited',
        'duration_minutes',
        'unit_score',
        'count',
        'whole_score',
        'max_benefit_quota_excess_score',
        'max_benefit_excess_score',
        'total_score',
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
        'bundle_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'provided_on' => 'datetime',
        'is_addition' => 'bool',
        'is_limited' => 'bool',
        'disposition' => CastsLtcsBillingServiceDetailDisposition::class,
        'service_code_category' => CastsLtcsServiceCodeCategory::class,
        'building_subtraction' => CastsLtcsBuildingSubtraction::class,
        'note_requirement' => CastsLtcsNoteRequirement::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingServiceDetail $domain
     * @param int $bundleId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainServiceDetail $domain, int $bundleId, int $sortOrder): self
    {
        $keys = [
            'bundle_id' => $bundleId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainServiceDetail
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainServiceDetail::fromAssoc($attrs);
    }
}
