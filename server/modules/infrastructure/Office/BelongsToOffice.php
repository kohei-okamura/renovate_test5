<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\Office\Office}.
 *
 * @property int $office_id 事業所ID
 * @property-read \Infrastructure\Office\Office $office 事業所
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOfficeId($value)
 * @mixin \Eloquent
 */
trait BelongsToOffice
{
    /**
     * BelongsTo: {@link \Infrastructure\Office\Office}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
