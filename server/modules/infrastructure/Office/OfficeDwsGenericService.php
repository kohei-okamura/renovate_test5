<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeDwsGenericService as DomainOfficeDwsGenericService;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所：障害福祉サービス Eloquent モデル.
 *
 * @property int $id 事業所：障害福祉サービスID
 * @property int $office_attr_id 事業所属性ID
 * @property int $dws_area_grade_id 障害地域区分ID
 * @property mixed $code 事業所番号
 * @property \Illuminate\Support\Carbon $opened_on 開設日
 * @property \Illuminate\Support\Carbon $designation_expired_on 指定更新期日
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereDesignationExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereDwsAreaGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsGenericService whereOpenedOn($value)
 * @mixin \Eloquent
 */
final class OfficeDwsGenericService extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_disability_welfare_service';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'dws_area_grade_id',
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
     * @param \Domain\Office\OfficeDwsGenericService $domain
     * @return \Infrastructure\Office\OfficeDwsGenericService
     */
    public static function fromDomain(DomainOfficeDwsGenericService $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOfficeDwsGenericService
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainOfficeDwsGenericService::create($attrs);
    }
}
