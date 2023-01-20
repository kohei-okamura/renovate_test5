<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Concretes;

use Domain\Context\Context;
use Domain\Organization\Organization;
use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use Illuminate\Support\Arr;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * テスト用の Context.
 */
class TestingContext implements Context
{
    private array $values = [];

    /**
     * 事前準備.
     *
     * @param \Tests\Unit\App\Http\Concretes\TestingContext $context
     * @param \Domain\Organization\Organization $organization
     * @param \ScalikePHP\Option $staff
     * @param bool $isAuthenticated
     * @return void
     */
    public static function prepare(
        TestingContext $context,
        Organization $organization,
        Option $staff,
        bool $isAuthenticated = true
    ): void {
        $context->values = compact('organization', 'staff', 'isAuthenticated');
    }

    /** {@inheritdoc} */
    public function isAuthorizedTo(Permission ...$permissions): bool
    {
        throw new LogicException('TestingContext::isAuthorizedTo must be mocked');
    }

    /** {@inheritdoc} */
    public function logContext(): array
    {
        return [];
    }

    /** {@inheritdoc} */
    public function uri(string $path): string
    {
        return "https://example.zinger.test/api/{$path}";
    }

    /** {@inheritdoc} */
    public function permitOfficeIds(): Seq
    {
        return Seq::empty();
    }

    /** {@inheritdoc} */
    public function isAccessibleTo(
        array|Permission $permission,
        int $organizationId,
        iterable $officeIds,
        int $staffId = 0
    ): bool {
        throw new LogicException('TestingContext::isAccessibleTo must be mocked');
    }

    /** {@inheritdoc} */
    public function getPermittedOffices(Permission $permission): Option
    {
        throw new LogicException('TestingContext::getPermittedOffices must be mocked');
    }

    /** {@inheritdoc} */
    public function hasRoleScope(RoleScope ...$requiredScopes): bool
    {
        throw new LogicException('TestingContext::hasRoleScope must be mocked');
    }

    /**
     * @param $name
     * @return array|\ArrayAccess|mixed
     */
    public function __get($name)
    {
        return Arr::get($this->values, $name);
    }
}
