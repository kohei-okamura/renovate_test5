<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http;

use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Organization\Organization;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Domain\Role\RoleScope;
use Domain\Staff\Staff;
use Lib\Exceptions\InvalidArgumentException;
use Lib\LazyField;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Context Implementation.
 */
final class HttpContext implements Context
{
    use LazyField;

    private bool $isSystemAdmin;
    private Organization $organization;
    private Seq $permissions;
    private string $uri;
    /** @var \Domain\Office\Office[]|\ScalikePHP\Seq 所属しているOffice */
    private Seq $offices;
    /** @var \Domain\Office\Office[]|\ScalikePHP\Seq アクセス許可のOffice */
    private Seq $groupOffices;
    /** @var \Domain\Role\Role[]|\ScalikePHP\Seq ロール */
    private Seq $roles;
    private Option $staffOption;

    /**
     * Context constructor.
     *
     * @param \Domain\Organization\Organization $organization
     * @param \Domain\Staff\Staff|\ScalikePHP\Option $staff
     * @param \ScalikePHP\Seq $roles
     * @param string $uri
     * @param \ScalikePHP\Seq $offices
     * @param \ScalikePHP\Seq $groupOffices
     */
    public function __construct(
        Organization $organization,
        Option $staff,
        Seq $roles,
        string $uri,
        Seq $offices,
        Seq $groupOffices
    ) {
        $this->isSystemAdmin = $this->isSystemAdmin($roles);
        $this->organization = $organization;
        $this->permissions = $this->isSystemAdmin ? Seq::emptySeq() : $this->getPermissions($roles);
        $this->roles = $roles;
        $this->uri = $uri;
        $this->offices = $offices;
        $this->groupOffices = $groupOffices;
        $this->staffOption = $staff;
    }

    /** {@inheritdoc} */
    public function isAccessibleTo(
        array|Permission $permission,
        int $organizationId,
        iterable $officeIds,
        int $staffId = 0
    ): bool {
        if ($this->organization->id !== $organizationId) {
            return false;
        }
        if ($this->isSystemAdmin) {
            return true;
        }

        $roles = $this->roles->filter(fn (Role $x): bool => $this->containsAnyPermissions($permission, $x));

        $p = fn (RoleScope $scope) => fn (Role $x): bool => $x->scope->value() === $scope->value();
        $officeIdSeq = Seq::fromArray($officeIds);
        return $roles->exists($p(RoleScope::whole()))
            || ($roles->exists($p(RoleScope::group()))
                && $officeIdSeq->nonEmpty()
                && $officeIdSeq->forAll(
                    fn (int $id): bool => $this->groupOffices->exists(fn (Office $x): bool => $x->id === $id)
                ))
            || ($roles->exists($p(RoleScope::office()))
                && $officeIdSeq->nonEmpty()
                && $officeIdSeq->forAll(
                    fn ($id): bool => $this->offices->exists(fn (Office $x): bool => $x->id === $id)
                ))
            || ($roles->exists($p(RoleScope::person()))
                && $this->staffOption->nonEmpty()
                && $this->staffOption->forAll(fn (Staff $x): bool => $x->id === $staffId));
    }

    /** {@inheritdoc} */
    public function isAuthorizedTo(Permission ...$permissions): bool
    {
        return $this->isSystemAdmin
            || Seq::fromArray($permissions)->forAll(
                fn (Permission $permission): bool => $this->permissions->contains($permission)
            );
    }

    /** {@inheritdoc} */
    public function logContext(): array
    {
        return [
            'organizationId' => $this->organization->id,
            'staffId' => $this->staffOption->pick('id')->getOrElseValue(''),
        ];
    }

    /** {@inheritdoc} */
    public function uri(string $path): string
    {
        return $this->uri . $path;
    }

    /** {@inheritdoc} */
    public function getPermittedOffices(Permission $permission): Option
    {
        if ($this->isSystemAdmin) {
            return Option::none();
        }
        // TODO: DEV-4033
        $roles = $this->roles->filter(
            fn (Role $x): bool => Seq::fromArray($x->permissions)->exists(
                fn (Permission $p) => $p->value() === $permission->value()
            )
        );
        $p = fn (RoleScope $scope) => fn (Role $x): bool => $x->scope->value() === $scope->value();
        return match (true) {
            $roles->exists($p(RoleScope::whole())) => Option::none(),
            $roles->exists($p(RoleScope::group())) => Option::from($this->groupOffices),
            $roles->exists($p(RoleScope::office())) => Option::from($this->offices),
            $roles->exists($p(RoleScope::person())) => Option::from($this->offices),
            default => throw new InvalidArgumentException("Permission({$permission->value()}) not permitted.")
        };
    }

    /** {@inheritdoc} */
    public function hasRoleScope(RoleScope ...$requiredScopes): bool
    {
        if ($this->isSystemAdmin) {
            return true;
        }

        $scopes = $this->roles
            ->map(fn (Role $x): int => $x->scope->value())
            ->distinct()
            ->toSeq();

        $required = Seq::fromArray($requiredScopes)
            ->map(fn (RoleScope $x): int => $x->value())
            ->toArray();

        return $scopes->exists(fn (int $x): bool => in_array($x, $required, true));
    }

    /**
     * @return bool
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAuthenticated(): bool
    {
        return $this->staffOption->isDefined();
    }

    /**
     * @return \Domain\Organization\Organization
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function organization(): Organization
    {
        return $this->organization;
    }

    /**
     * @return \ScalikePHP\Option
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function staff(): Option
    {
        return $this->staffOption;
    }

    /**
     * 指定したロールの一覧にシステム管理者ロールが含まれるかどうかを判定する.
     *
     * @param \ScalikePHP\Seq $roles
     * @return bool
     */
    private function isSystemAdmin(Seq $roles): bool
    {
        return $roles->exists(fn (Role $role): bool => $role->isSystemAdmin);
    }

    /**
     * 指定したロールの一覧からパーミッションの一覧を生成する.
     *
     * @param \ScalikePHP\Seq $roles
     * @return \Domain\Permission\Permission[]|\ScalikePHP\Seq
     */
    private function getPermissions(Seq $roles): Seq
    {
        return $roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->distinctBy(fn (Permission $permission) => $permission->value());
    }

    /**
     * ロールが指定された権限のうち少なくとも一つは持っているか判定する.
     *
     * @param array|\Domain\Permission\Permission|\Domain\Permission\Permission[] $permission
     * @param \Domain\Role\Role $role
     * @return bool
     */
    private function containsAnyPermissions($permission, Role $role): bool
    {
        $permissions = Seq::fromArray(is_array($permission) ? $permission : [$permission]);
        // TODO: DEV-4033 serialize/unserialize によって比較できない問題を回避するために ->value() を付けてる
        return Seq::fromArray($role->permissions)->exists(
            fn (Permission $x): bool => $permissions->exists(
                fn (Permission $y): bool => $y->value() === $x->value()
            )
        );
    }

    /**
     * シリアライズを指定する.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'isSystemAdmin' => $this->isSystemAdmin,
            'organization' => $this->organization,
            'permissions' => $this->permissions->toArray(),
            'uri' => $this->uri,
            'offices' => $this->offices->toArray(),
            'groupOffices' => $this->groupOffices->toArray(),
            'roles' => $this->roles->toArray(),
            'staffOption' => $this->staffOption,
        ];
    }

    /**
     * デシリアライズを指定する.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->isSystemAdmin = $data['isSystemAdmin'];
        $this->organization = $data['organization'];
        $this->permissions = Seq::fromArray($data['permissions']);
        $this->uri = $data['uri'];
        $this->offices = Seq::fromArray($data['offices']);
        $this->groupOffices = Seq::fromArray($data['groupOffices']);
        $this->roles = Seq::fromArray($data['roles']);
        $this->staffOption = $data['staffOption'];
    }
}
