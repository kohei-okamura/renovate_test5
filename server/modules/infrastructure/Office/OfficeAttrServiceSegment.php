<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Common\ServiceSegment;
use Infrastructure\Model;

/**
 * 事業所属性に属するサービス領域 Eloquent モデル.
 *
 * @property int $office_attr_id 事業所属性ID
 * @property int $service_segment 事業領域
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment whereServiceSegment($value)
 * @mixin \Eloquent
 */
final class OfficeAttrServiceSegment extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_attr_service_segment';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'office_attr_id',
        'service_segment',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Common\ServiceSegment $segment
     * @return \Infrastructure\Office\OfficeAttrServiceSegment
     */
    public static function fromDomain(ServiceSegment $segment): self
    {
        return self::newModelInstance(['service_segment' => $segment]);
    }
}
