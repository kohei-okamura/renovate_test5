<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Resolvers;

use Domain\Organization\OrganizationRepository;
use Laravel\Lumen\Http\Request as LumenRequest;
use ScalikePHP\Option;

/**
 * OrganizationResolver Implementation.
 */
final class OrganizationResolverImpl implements OrganizationResolver
{
    private OrganizationRepository $repository;

    /**
     * OrganizationResolverImpl constructor.
     *
     * @param \Domain\Organization\OrganizationRepository $repository
     */
    public function __construct(OrganizationRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function resolve(LumenRequest $request): Option
    {
        $segments = explode('.', $request->getHttpHost());
        return $this->repository->lookupOptionByCode($segments[0]);
    }
}
