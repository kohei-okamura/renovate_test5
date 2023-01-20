<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCard as DomainLtcsInsCard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Model;
use ScalikePHP\Map;

/**
 * 介護保険被保険者証属性 Eloquent モデル.
 *
 * @property int $id 介護保険被保険者証属性ID
 * @property int $ltcs_ins_card_id 介護保険被保険者証ID
 * @property \Domain\LtcsInsCard\LtcsInsCardStatus $status 介護保険認定区分
 * @property \Domain\LtcsInsCard\LtcsLevel $ltcs_level 要介護度・要介護状態区分等
 * @property string $ins_number 被保険者証番号
 * @property string $insurer_number 保険者番号
 * @property string $insurer_name 保険者名
 * @property int $copay_rate 利用者負担割合（原則）
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property \Domain\Common\Carbon $issued_on 交付日
 * @property \Domain\Common\Carbon $certificated_on 認定日
 * @property \Domain\Common\Carbon $activated_on 認定の有効期間（開始）
 * @property \Domain\Common\Carbon $deactivated_on 認定の有効期間（終了）
 * @property \Domain\Common\Carbon $copay_activated_on 利用者負担適用期間（開始）
 * @property \Domain\Common\Carbon $copay_deactivated_on 利用者負担適用期間（終了）
 * @property string $care_manager_name 居宅介護支援事業所：担当者
 * @property \Domain\LtcsInsCard\LtcsCarePlanAuthorType $care_plan_author_type 居宅サービス計画作成区分
 * @property null|int $community_general_support_center_id 地域包括支援センター ID
 * @property null|int $care_plan_author_office_id 居宅介護支援事業所 ID
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Infrastructure\LtcsInsCard\LtcsInsCard $ltcsInsCard
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\LtcsInsCard\LtcsInsCardMaxBenefitQuota[] $maxBenefitQuotas
 * @property-read null|int $max_benefit_quotas_count
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereCertificatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereCopayActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereCopayDeactivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereCopayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereDeactivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereEffectivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereInsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereInsurerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereInsurerNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereIssuedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereLtcsInsCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereLtcsInsCardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereLtcsLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtcsInsCardAttr whereVersion($value)
 * @mixin \Eloquent
 */
final class LtcsInsCardAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_ins_card_attr';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'status',
        'ltcs_level',
        'ins_number',
        'insurer_number',
        'insurer_name',
        'copay_rate',
        'effectivated_on',
        'issued_on',
        'certificated_on',
        'activated_on',
        'deactivated_on',
        'copay_activated_on',
        'copay_deactivated_on',
        'care_manager_name',
        'care_plan_author_type',
        'community_general_support_center_id',
        'care_plan_author_office_id',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'effectivated_on' => 'date',
        'issued_on' => 'date',
        'certificated_on' => 'date',
        'activated_on' => 'date',
        'deactivated_on' => 'date',
        'copay_activated_on' => 'date',
        'copay_deactivated_on' => 'date',
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
        'status' => CastsLtcsInsCardStatus::class,
        'care_plan_author_type' => CastsLtcsCarePlanAuthorType::class,
        'ltcs_level' => CastsLtcsLevel::class,
    ];

    /**
     * BelongsTo: {@link \Infrastructure\LtcsInsCard\LtcsInsCard}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @noinspection PhpUnused
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function ltcsInsCard(): BelongsTo
    {
        return $this->belongsTo(LtcsInsCard::class);
    }

    /**
     * HasMany: {@link \Infrastructure\LtcsInsCard\LtcsInsCardMaxBenefitQuota}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maxBenefitQuotas(): HasMany
    {
        return $this->hasMany(LtcsInsCardMaxBenefitQuota::class);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'maxBenefitQuotas',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $domain
     * @return \Infrastructure\LtcsInsCard\LtcsInsCardAttr
     */
    public static function fromDomain(DomainLtcsInsCard $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        $maxBenefitQuotas = Map::from($domain->maxBenefitQuotas)
            ->mapValues(function ($maxBenefitQuota, $key) {
                return LtcsInsCardMaxBenefitQuota::fromDomain($maxBenefitQuota, ['sort_order' => $key]);
            })
            ->toAssoc();
        return self::newModelInstance($attrs)->setRelation('maxBenefitQuotas', $maxBenefitQuotas);
    }

    /**
     * Get mutator for max benefit quota attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getMaxBenefitQuotasAttribute(): array
    {
        return $this->mapSortRelation(
            'maxBenefitQuotas',
            'sort_order',
            fn (LtcsInsCardMaxBenefitQuota $x) => $x->toDomain()
        );
    }
}
