<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Domain\Role\RoleScope;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Map;

/**
 * ロール作成リクエスト.
 *
 * @property-read string $name
 * @property-read bool $isSystemAdmin
 * @property-read bool[] $permissions
 * @property-read int $scope
 * @property-read int $sortOrder
 */
class CreateRoleRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * ロールを生成する.
     *
     * @return \Domain\Role\Role
     */
    public function payload(): Role
    {
        $permissions = $this->isSystemAdmin
            ? []
            : Map::from($this->permissions)
                ->filter(fn (bool $x, string $key): bool => $x) // trueに指定されたものに絞る
                ->keys()
                ->map(fn (string $x): Permission => Permission::from($x))
                ->toArray();
        return Role::create([
            'permissions' => $permissions,
            'name' => $this->name,
            'isSystemAdmin' => $this->isSystemAdmin,
            'scope' => RoleScope::from($this->scope),
            'sortOrder' => Carbon::now()->timestamp,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'name' => ['required', 'max:100'],
            'permissions' => $input['isSystemAdmin'] ? [] : ['bail', 'required', 'permissions', 'authorized_permissions'],
            'isSystemAdmin' => ['required', 'boolean'],
            'scope' => ['required', 'role_scope'],
        ];
    }
}
