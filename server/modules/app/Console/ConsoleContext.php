<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console;

use Domain\Context\Context;
use Domain\Organization\Organization;
use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use Lib\Exceptions\NotFoundException;
use Lib\LazyField;
use ScalikePHP\Option;
use UseCase\Organization\LookupOrganizationByCodeUseCase;

/**
 * Context Implementation.
 */
final class ConsoleContext implements Context
{
    use LazyField;

    private Organization $organization;

    /**
     * Context constructor.
     *
     * @param \Domain\Organization\Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * 事業者コードを指定してインスタンスを生成する.
     *
     * @param string $organizationCode
     * @return \Domain\Context\Context
     */
    public static function create(string $organizationCode): Context
    {
        return app()->call(function (LookupOrganizationByCodeUseCase $useCase) use ($organizationCode): Context {
            return $useCase
                ->handle($organizationCode)
                ->map(fn (Organization $organization): Context => new self($organization))
                ->getOrElse(function () use ($organizationCode): void {
                    throw new NotFoundException("Organization({$organizationCode}) not found");
                });
        });
    }

    /** {@inheritdoc} */
    public function isAccessibleTo(
        array|Permission $permission,
        int $organizationId,
        iterable $officeIds,
        int $staffId = 0
    ): bool {
        return $this->organization->id === $organizationId;
    }

    /** {@inheritdoc} */
    public function isAuthorizedTo(Permission ...$permissions): bool
    {
        return true;
    }

    /** {@inheritdoc} */
    public function logContext(): array
    {
        return [
            'organizationId' => $this->organization->id,
            'staffId' => '',
        ];
    }

    /** {@inheritdoc} */
    public function uri(string $path): string
    {
        // TODO: DEV-2312 URL の生成方法を検討して実装する. HttpContext とやり方を統一するべき……？
        return '';
    }

    /** {@inheritdoc} */
    public function getPermittedOffices(Permission $permission): Option
    {
        return Option::none();
    }

    /** {@inheritdoc} */
    public function hasRoleScope(RoleScope ...$requiredScopes): bool
    {
        return true;
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
        return Option::none();
    }

    /**
     * @return bool
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAuthenticated(): bool
    {
        return true;
    }

    /**
     * シリアライズを指定する.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'organization' => $this->organization,
        ];
    }

    /**
     * デシリアライズを指定する.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->organization = $data['organization'];
    }
}
