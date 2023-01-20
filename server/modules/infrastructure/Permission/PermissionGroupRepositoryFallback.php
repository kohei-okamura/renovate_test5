<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\PermissionGroupRepository;

/**
 * Permission Group Repository Fallback Interface.
 */
interface PermissionGroupRepositoryFallback extends PermissionGroupRepository
{
}
