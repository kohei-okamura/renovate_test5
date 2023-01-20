<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Common\LocationResolver;
use Infrastructure\Common\LocationResolverImpl;

/**
 * Location Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class LocationDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            LocationResolver::class => LocationResolverImpl::class,
        ];
    }
}
