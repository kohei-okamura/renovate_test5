<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Organization\OrganizationRepository;
use ScalikePHP\Option;

/**
 * コードから事業者取得実装.
 */
class LookupOrganizationByCodeInteractor implements LookupOrganizationByCodeUseCase
{
    private OrganizationRepository $repository;

    public function __construct(OrganizationRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(string $code): Option
    {
        return $this->repository->lookupOptionByCode($code);
    }
}
