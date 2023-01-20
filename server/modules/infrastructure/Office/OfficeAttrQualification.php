<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeQualification;
use Infrastructure\Model;

/**
 * 事業所属性に属する指定区分 Eloquent モデル.
 *
 * @property int $office_attr_id 事業所属性ID
 * @property string $qualification 指定区分
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment whereOfficeAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeAttrServiceSegment whereServiceSegment($value)
 * @mixin \Eloquent
 */
final class OfficeAttrQualification extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'office_attr_office_qualification';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'office_attr_id',
        'qualification',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\OfficeQualification $qualification
     * @return \Infrastructure\Office\OfficeAttrQualification
     */
    public static function fromDomain(OfficeQualification $qualification): self
    {
        return self::newModelInstance(['qualification' => $qualification]);
    }
}
