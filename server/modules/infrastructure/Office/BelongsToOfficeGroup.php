<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\Office\OfficeGroup}.
 *
 * @property int $office_group_id 事業所グループID
 * @property-read \Infrastructure\Office\OfficeGroup $officeGroup 事業所グループ
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOfficeGroupId($value)
 * @mixin \Eloquent
 */
trait BelongsToOfficeGroup
{
    /**
     * BelongsTo: {@link \Infrastructure\Office\OfficeGroup}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function officeGroup(): BelongsTo
    {
        return $this->belongsTo(OfficeGroup::class);
    }
}
