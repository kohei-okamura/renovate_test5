<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Certification;
use Infrastructure\Model;

/**
 * スタッフ属性・資格中間テーブル Eloquent モデル.
 *
 * @property int $staff_attr_id
 * @property int $certification
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttrCertification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttrCertification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttrCertification query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttrCertification whereStaffAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffAttrCertification whereCertification($value)
 * @mixin \Eloquent
 */
final class StaffAttrCertification extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'staff_attr_certification';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'staff_attr_id',
        'certification',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Staff\Certification $certification
     * @return \Infrastructure\Staff\StaffAttrCertification
     */
    public static function fromDomain(Certification $certification): self
    {
        return self::newModelInstance(['certification' => $certification]);
    }
}
