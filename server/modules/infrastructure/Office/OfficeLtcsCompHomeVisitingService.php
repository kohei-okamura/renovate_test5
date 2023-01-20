<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeLtcsCompHomeVisitingService as DomainOfficeLtcsCompHomeVisitingService;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所：介護保険サービス：訪問型サービス（総合事業） Eloquent モデル.
 *
 * @property int $id 事業所：介護保険サービス：訪問型サービス（総合事業）ID
 * @property int $office_attr_id 事業所属性ID
 * @property mixed $code 事業所番号
 * @property \Illuminate\Support\Carbon $opened_on 開設日
 * @property \Illuminate\Support\Carbon $designation_expired_on 指定更新期日
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService whereDesignationExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeLtcsCompHomeVisitingService whereOpenedOn($value)
 * @mixin \Eloquent
 */
final class OfficeLtcsCompHomeVisitingService extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_ltcs_comp_home_visiting_service';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'code',
        'opened_on',
        'designation_expired_on',
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
     * @param \Domain\Office\OfficeLtcsCompHomeVisitingService $domain
     * @return \Infrastructure\Office\OfficeLtcsCompHomeVisitingService
     */
    public static function fromDomain(DomainOfficeLtcsCompHomeVisitingService $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOfficeLtcsCompHomeVisitingService
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainOfficeLtcsCompHomeVisitingService::create($attrs);
    }
}
