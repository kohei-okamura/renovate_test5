<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeDwsCommAccompanyService as DomainOfficeDwsCommAccompanyService;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 事業所：障害福祉サービス（地域生活支援事業・移動支援） Eloquent モデル.
 *
 * @property int $id 事業所：障害福祉サービス（地域生活支援事業・移動支援）ID
 * @property int $office_attr_id 事業所属性ID
 * @property mixed $code 事業所番号
 * @property \Illuminate\Support\Carbon $opened_on 開設日
 * @property \Illuminate\Support\Carbon $designation_expired_on 指定更新期日
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService whereDesignationExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeDwsCommAccompanyService whereOpenedOn($value)
 * @mixin \Eloquent
 */
final class OfficeDwsCommAccompanyService extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_dws_comm_accompany_service';

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
     * @param \Domain\Office\OfficeDwsCommAccompanyService $domain
     * @return \Infrastructure\Office\OfficeDwsCommAccompanyService
     */
    public static function fromDomain(DomainOfficeDwsCommAccompanyService $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOfficeDwsCommAccompanyService
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainOfficeDwsCommAccompanyService::create($attrs);
    }
}
