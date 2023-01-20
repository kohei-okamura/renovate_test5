<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification as DomainDwsCertification;
use Domain\DwsCertification\DwsType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Model;

/**
 * 障害福祉サービス受給者証属性 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス受給者証属性ID
 * @property int $dws_certification_id 障害福祉サービス受給者証ID
 * @property \Domain\DwsCertification\DwsLevel $dws_level 障害程度区分
 * @property \Domain\DwsCertification\DwsCertificationStatus $status 障害福祉サービス認定区分
 * @property \Domain\DwsCertification\CopayCoordination $copayCoordination 上限管理
 * @property \Domain\DwsCertification\Child $child 児童情報
 * @property string $dws_number 受給者証番号
 * @property string $city_code 市区町村番号
 * @property string $city_name 市区町村名
 * @property int $copay_rate 利用者負担割合（原則）
 * @property int $copay_limit 負担上限月額
 * @property null|int office_id 上限管理事業所ID
 * @property bool $is_subject_of_comprehensive_support 重度障害者等包括支援対象フラグ
 * @property \Domain\Common\Carbon $activated_on 認定の有効期間（開始）
 * @property \Domain\Common\Carbon $deactivated_on 認定の有効期間（終了）
 * @property \Domain\Common\Carbon $issued_on 交付日
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property \Domain\Common\Carbon $copay_activated_on 利用者負担適用期間（開始）
 * @property \Domain\Common\Carbon $copay_deactivated_on 利用者負担適用期間（終了)
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\DwsCertification\DwsCertificationAttrDwsType[] $dwsTypes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\DwsCertification\DwsCertificationAgreement[] $agreements
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\DwsCertification\DwsCertificationGrant[] $grants
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCopayActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCopayDeactivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCopayLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCopayRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDeactivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDwsCertificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDwsCertificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDwsLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereCopayCoordinationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDwsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereDwsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereEffectivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereIsSubjectOfComprehensiveSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereIssuedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAttr whereVersion($value)
 * @mixin \Eloquent
 */
final class DwsCertificationAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_certification_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_certification_id',
        'dws_level',
        'status',
        'copay_coordination',
        'child',
        'dws_number',
        'city_code',
        'city_name',
        'copay_rate',
        'copay_limit',
        'is_subject_of_comprehensive_support',
        'activated_on',
        'deactivated_on',
        'issued_on',
        'effectivated_on',
        'copay_activated_on',
        'copay_deactivated_on',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'dws_level' => CastsDwsLevel::class,
        'status' => CastsDwsCertificationStatus::class,
        'is_subject_of_comprehensive_support' => 'boolean',
        'activated_on' => 'date',
        'deactivated_on' => 'date',
        'issued_on' => 'date',
        'effectivated_on' => 'date',
        'copay_activated_on' => 'date',
        'copay_deactivated_on' => 'date',
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * BelongsTo: {@link \Infrastructure\DwsCertification\DwsCertification}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @noinspection PhpUnused
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function dwsCertification(): BelongsTo
    {
        return $this->belongsTo(DwsCertification::class);
    }

    /**
     * HasMany: {@link \Infrastructure\DwsCertification\DwsCertificationAttrDwsType}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @noinspection PhpUnused
     */
    public function dwsTypes(): HasMany
    {
        return $this->hasMany(DwsCertificationAttrDwsType::class);
    }

    /**
     * HasMany: {@link \Infrastructure\DwsCertification\DwsCertificationGrant}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grants(): HasMany
    {
        return $this->hasMany(DwsCertificationGrant::class)->orderBy('sort_order');
    }

    /** HasMany: {@link \Infrastructure\DwsCertification\DwsCertificationAgreement}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agreements(): HasMany
    {
        return $this->hasMany(DwsCertificationAgreement::class)->orderBy('sort_order');
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'agreements',
            'child',
            'copayCoordination',
            'dwsTypes',
            'grants',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsCertification\DwsCertification $domain
     * @return \Infrastructure\DwsCertification\DwsCertificationAttr
     */
    public static function fromDomain(DomainDwsCertification $domain): self
    {
        $keys = [
            'dws_level',
            'status',
            'copay_coordination',
            'child',
            'dws_number',
            'city_code',
            'city_name',
            'copay_rate',
            'copay_limit',
            'is_subject_of_comprehensive_support',
            'activated_on',
            'deactivated_on',
            'issued_on',
            'effectivated_on',
            'copay_activated_on',
            'copay_deactivated_on',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for dws_types attribute.
     *
     * @return array|\Domain\DwsCertification\DwsType[]
     * @noinspection PhpUnused
     */
    protected function getDwsTypesAttribute(): array
    {
        return $this->mapRelation(
            'dwsTypes',
            fn (DwsCertificationAttrDwsType $x): DwsType => DwsType::from($x->dws_type)
        );
    }

    /**
     * Get mutator for status attribute.
     *
     * @return \Domain\DwsCertification\CopayCoordination
     * @noinspection PhpUnused
     */
    protected function getCopayCoordinationAttribute(): CopayCoordination
    {
        return CopayCoordination::create([
            'copayCoordinationType' => CopayCoordinationType::from($this->attributes['copay_coordination_type']),
            'officeId' => $this->attributes['copay_coordination_office_id'],
        ]);
    }

    /**
     * Set mutator for CopayCoordinationType attribute.
     *
     * @param \Domain\DwsCertification\CopayCoordination $copayCoordination
     * @return void
     * @noinspection PhpUnused
     */
    protected function setCopayCoordinationAttribute(CopayCoordination $copayCoordination): void
    {
        $this->attributes['copay_coordination_type'] = $copayCoordination->copayCoordinationType;
        $this->attributes['copay_coordination_office_id'] = $copayCoordination->officeId;
    }

    /**
     * Get mutator for status attribute.
     *
     * @return \Domain\DwsCertification\Child
     * @noinspection PhpUnused
     */
    protected function getChildAttribute(): Child
    {
        return Child::create([
            'name' => new StructuredName(
                familyName: $this->attributes['child_family_name'],
                givenName: $this->attributes['child_given_name'],
                phoneticFamilyName: $this->attributes['child_phonetic_family_name'],
                phoneticGivenName: $this->attributes['child_phonetic_given_name'],
            ),
            'birthday' => isset($this->attributes['child_birthday'])
                ? Carbon::parse($this->attributes['child_birthday'])
                : null,
        ]);
    }

    /**
     * Set mutator for CopayCoordinationType attribute.
     *
     * @param \Domain\DwsCertification\Child $child
     * @return void
     * @noinspection PhpUnused
     */
    protected function setChildAttribute(Child $child): void
    {
        $this->attributes['child_family_name'] = $child->name->familyName;
        $this->attributes['child_given_name'] = $child->name->givenName;
        $this->attributes['child_phonetic_family_name'] = $child->name->phoneticFamilyName;
        $this->attributes['child_phonetic_given_name'] = $child->name->phoneticGivenName;
        $this->attributes['child_birthday'] = $child->birthday;
    }

    /**
     * Get mutator for dws certification agreement.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getAgreementsAttribute(): array
    {
        return $this->mapRelation('agreements', fn (DwsCertificationAgreement $x) => $x->toDomain());
    }

    /**
     * Get mutator for dws certification grant.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getGrantsAttribute(): array
    {
        return $this->mapRelation('grants', fn (DwsCertificationGrant $x) => $x->toDomain());
    }
}
