<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console;

use Closure;
use Domain\Organization\Organization;
use UseCase\Organization\GetAllValidOrganizationUseCase;

/**
 * {@link \App\Console\OrganizationIterator} 実装.
 */
final class OrganizationIteratorImpl implements OrganizationIterator
{
    private GetAllValidOrganizationUseCase $useCase;

    /**
     * {@link \App\Console\OrganizationIteratorImpl} constructor.
     *
     * @param \UseCase\Organization\GetAllValidOrganizationUseCase $useCase
     */
    public function __construct(GetAllValidOrganizationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function iterate(Closure $f): void
    {
        $this->useCase->handle()->each(function (Organization $organization) use ($f): void {
            $context = new ConsoleContext($organization);
            $f($context);
        });
    }
}
