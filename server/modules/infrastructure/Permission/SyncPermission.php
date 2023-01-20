<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\Permission;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ScalikePHP\Seq;

/**
 * Eloquent モデル向けパーミッション同期処理.
 *
 * @property-read \Domain\Permission\Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
 * @mixin \Eloquent
 */
trait SyncPermission
{
    /**
     * HasMany: Permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function permissions(): HasMany;

    /**
     * 権限の一覧を同期する.
     *
     * @param \Domain\Permission\Permission[]|iterable $permissions
     * @return void
     */
    public function syncPermissions(iterable $permissions): void
    {
        $old = Seq::fromArray($this->permissions);
        $new = Seq::fromArray($permissions);
        $xs = $new->filterNot(fn (Permission $x): bool => $old->contains($x));
        $this->permissions()->whereNotIn('permission', $new->toArray())->delete();
        foreach ($xs as $permission) {
            $this->permissions()->create(compact('permission'));
        }
        $this->refresh();
    }
}
