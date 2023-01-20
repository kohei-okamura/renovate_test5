<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\Office as DomainOffice;
use Domain\Office\OfficeDwsCommAccompanyService as DomainDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService as DomainDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService as DomainLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService as DomainLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService as DomainLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeLtcsPreventionService as DomainLtcsPreventionService;
use Domain\Office\OfficeQualification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Common\AddrHolder;
use Infrastructure\Common\LocationHolder;
use Infrastructure\Model;

/**
 * 事業所属性 Eloquent モデル.
 *
 * @property int $id 事業所属性ID
 * @property null|int $office_group_id 事業所グループID
 * @property string $name 事業所名
 * @property string $abbr 略称
 * @property string $phonetic_name フリガナ
 * @property string $corporation_name 法人名
 * @property string $phonetic_corporation_name 法人名：フリガナ
 * @property-read int $purpose 事業者区分
 * @property string $addr_postcode 郵便番号
 * @property int $addr_prefecture 都道府県
 * @property string $addr_city 市区町村
 * @property string $addr_street 町名・番地
 * @property string $addr_apartment 建物名など
 * @property null|float $location_lat 緯度
 * @property null|float $location_lng 経度
 * @property string $tel 電話番号
 * @property string $fax FAX番号
 * @property string $email メールアドレス
 * @property-read int $status 状態
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read array|\Domain\Office\OfficeQualification[] $qualifications
 * @property-read null|\Domain\Office\OfficeDwsGenericService $dwsGenericService
 * @property-read null|DomainDwsCommAccompanyService $dwsCommAccompanyService
 * @property-read null|DomainLtcsCareManagementService $ltcsCareManagementService
 * @property-read null|DomainLtcsHomeVisitLongTermCareService $ltcsHomeVisitLongTermCareService
 * @property-read null|DomainLtcsCompHomeVisitingService $ltcsCompHomeVisitingService
 * @property-read null|DomainLtcsPreventionService $ltcsPreventionService
 * @mixin \Eloquent
 */
final class OfficeAttr extends Model
{
    use AddrHolder;
    use BelongsToOffice;
    use BelongsToOfficeGroup;
    use LocationHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'office_attr';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'office_group_id',
        'name',
        'abbr',
        'phonetic_name',
        'corporation_name',
        'phonetic_corporation_name',
        'purpose',
        'addr',
        'location',
        'tel',
        'fax',
        'email',
        'status',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'dws_generic_service',
        'dws_comm_accompany_service',
        'ltcs_home_visit_long_term_care_service',
        'ltcs_care_management_service',
        'ltcs_comp_home_visiting_service',
        'ltcs_prevention_service',
        'qualifications',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'office_id',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'purpose' => CastsPurpose::class,
        'status' => CastsOfficeStatus::class,
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeDwsGenericService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dwsGenericService(): HasOne
    {
        return $this->hasOne(OfficeDwsGenericService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeDwsCommAccompanyService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dwsCommAccompanyService(): HasOne
    {
        return $this->hasOne(OfficeDwsCommAccompanyService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeLtcsHomeVisitLongTermCareService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ltcsHomeVisitLongTermCareService(): HasOne
    {
        return $this->hasOne(OfficeLtcsHomeVisitLongTermCareService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeLtcsCareManagementService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ltcsCareManagementService(): HasOne
    {
        return $this->hasOne(OfficeLtcsCareManagementService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeLtcsCompHomeVisitingService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ltcsCompHomeVisitingService(): HasOne
    {
        return $this->hasOne(OfficeLtcsCompHomeVisitingService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeLtcsPreventionService}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ltcsPreventionService(): HasOne
    {
        return $this->hasOne(OfficeLtcsPreventionService::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Office\OfficeAttrQualification}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(OfficeAttrQualification::class);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        return $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\Office $domain
     * @return \Infrastructure\Office\OfficeAttr
     */
    public static function fromDomain(DomainOffice $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for dws_generic_service_attribute attribute.
     *
     * @return null|\Domain\Office\OfficeDwsGenericService
     * @noinspection PhpUnused
     */
    protected function getDwsGenericServiceAttribute(): ?DomainDwsGenericService
    {
        $x = $this->getRelationValue('dwsGenericService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for dws_comm_accompany_service attribute.
     *
     * @return null|\Domain\Office\OfficeDwsCommAccompanyService
     * @noinspection PhpUnused
     */
    protected function getDwsCommAccompanyServiceAttribute(): ?DomainDwsCommAccompanyService
    {
        $x = $this->getRelationValue('dwsCommAccompanyService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for ltcs_home_visit_long_term_care_service attribute.
     *
     * @return null|\Domain\Office\OfficeLtcsHomeVisitLongTermCareService
     * @noinspection PhpUnused
     */
    protected function getLtcsHomeVisitLongTermCareServiceAttribute(): ?DomainLtcsHomeVisitLongTermCareService
    {
        $x = $this->getRelationValue('ltcsHomeVisitLongTermCareService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for ltcs_care_management_service attribute.
     *
     * @return null|\Domain\Office\OfficeLtcsCareManagementService
     * @noinspection PhpUnused
     */
    protected function getLtcsCareManagementServiceAttribute(): ?DomainLtcsCareManagementService
    {
        $x = $this->getRelationValue('ltcsCareManagementService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for ltcs_comp_home_visiting_service attribute.
     *
     * @return null|\Domain\Office\OfficeLtcsCompHomeVisitingService
     * @noinspection PhpUnused
     */
    protected function getLtcsCompHomeVisitingServiceAttribute(): ?DomainLtcsCompHomeVisitingService
    {
        $x = $this->getRelationValue('ltcsCompHomeVisitingService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for ltcs_prevention_service attribute.
     *
     * @return null|\Domain\Office\OfficeLtcsPreventionService
     * @noinspection PhpUnused
     */
    protected function getLtcsPreventionServiceAttribute(): ?DomainLtcsPreventionService
    {
        $x = $this->getRelationValue('ltcsPreventionService');
        return $x === null ? null : $x->toDomain();
    }

    /**
     * Get mutator for qualifications attribute.
     *
     * @return array|\Domain\Common\ServiceSegment[]
     * @noinspection PhpUnused
     */
    protected function getQualificationsAttribute(): array
    {
        return $this->mapRelation(
            'qualifications',
            fn (OfficeAttrQualification $x) => OfficeQualification::from($x->qualification)
        );
    }
}
