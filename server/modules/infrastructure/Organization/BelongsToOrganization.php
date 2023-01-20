<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\Organization\Organization}.
 *
 * @property-read int $organization_id 事業者ID
 * @property-read \Infrastructure\Organization\Organization $organization 事業者
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOrganizationId($value)
 * @mixin \Eloquent
 */
trait BelongsToOrganization
{
    /**
     * BelongsTo: {@link \Infrastructure\Organization\Organization}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
