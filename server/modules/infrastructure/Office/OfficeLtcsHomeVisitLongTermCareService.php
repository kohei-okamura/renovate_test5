<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeLtcsHomeVisitLongTermCareService as DomainOfficeLtcsHomeVisitLongTermCareService;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所：介護保険サービス：訪問介護 Eloquent モデル.
 *
 * @property int $id 事業所：介護保険サービス：訪問介護ID
 * @property int $office_attr_id 事業所属性ID
 * @property mixed $code 事業所番号
 * @property \Illuminate\Support\Carbon $opened_on 開設日
 * @property \Illuminate\Support\Carbon $designation_expired_on 指定更新期日
 * @property int $ltcs_area_grade_id 介保地域区分ID
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereDesignationExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereLtcsAreaGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCareManagementService whereOpenedOn($value)
 * @mixin \Eloquent
 */
final class OfficeLtcsHomeVisitLongTermCareService extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_ltcs_home_visit_long_term_care_service';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'code',
        'opened_on',
        'designation_expired_on',
        'ltcs_area_grade_id',
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
        'office_attr_id',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'opened_on' => 'date',
        'designation_expired_on' => 'date',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\OfficeLtcsHomeVisitLongTermCareService $domain
     * @return \Infrastructure\Office\OfficeLtcsHomeVisitLongTermCareService
     */
    public static function fromDomain(DomainOfficeLtcsHomeVisitLongTermCareService $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOfficeLtcsHomeVisitLongTermCareService
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainOfficeLtcsHomeVisitLongTermCareService::create($attrs);
    }
}
