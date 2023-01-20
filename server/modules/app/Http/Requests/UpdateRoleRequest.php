<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Map;

/**
 * ロール更新リクエスト.
 *
 * @property-read string $name
 * @property-read bool $isSystemAdmin
 * @property-read bool[] $permissions
 * @property-read int $scope
 * @property-read int $sortOrder
 */
class UpdateRoleRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        $permissions = $this->isSystemAdmin
            ? []
            : Map::from($this->permissions)
                ->filter(fn (bool $x, string $key): bool => $x) // trueに指定されたものだけに絞る
                ->keys()
                ->map(fn (string $x): Permission => Permission::from($x))
                ->toArray();
        return [
            'name' => $this->name,
            'permissions' => $permissions,
            'isSystemAdmin' => $this->isSystemAdmin,
            'scope' => RoleScope::from($this->scope),
        ];
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
