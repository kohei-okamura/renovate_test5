<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Permission\PermissionGroupRepository;
use Infrastructure\Repository\CacheRepository;
use Lib\Exceptions\LogicException;

/**
 * PermissionGroupRepository cache implementation.
 */
final class PermissionGroupRepositoryCacheImpl extends CacheRepository implements PermissionGroupRepository
{
    /**
     * PermissionGroupRepositoryCacheImpl constructor.
     *
     * @param \Infrastructure\Permission\PermissionGroupRepositoryFallback $fallback
     */
    public function __construct(PermissionGroupRepositoryFallback $fallback)
    {
        parent::__construct($fallback);
    }

    /** {@inheritdoc} */
    public function store(mixed $entity): never
    {
        throw new LogicException('PermissionGroup can not store to repository');
    }

    /** {@inheritdoc} */
    public function remove(mixed $entity): never
    {
        throw new LogicException('PermissionGroup can not remove from repository');
    }

    /** {@inheritdoc} */
    public function removeById(int ...$ids): never
    {
        throw new LogicException('PermissionGroup can not remove from repository');
    }

    /** {@inheritdoc} */
    protected function expiredAt(): DateTimeInterface
    {
        return Carbon::tomorrow();
    }

    /** {@inheritdoc} */
    protected function namespace(): string
    {
        return 'permissionGroup';
    }
}
