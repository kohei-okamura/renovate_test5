<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\Staff\Staff}.
 *
 * @property int $staff_id スタッフID
 * @property-read \Infrastructure\Staff\Staff $staff スタッフ
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStaffId($value)
 * @mixin \Eloquent
 */
trait BelongsToStaff
{
    /**
     * BelongsTo: {@link \Infrastructure\Staff\Staff}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
