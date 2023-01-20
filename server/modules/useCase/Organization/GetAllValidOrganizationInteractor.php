<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Organization\OrganizationFinder;
use ScalikePHP\Seq;

/**
 * 有効な事業者取得実装.
 */
class GetAllValidOrganizationInteractor implements GetAllValidOrganizationUseCase
{
    private OrganizationFinder $finder;

    /**
     * Constructor.
     *
     * @param \Domain\Organization\OrganizationFinder $finder
     */
    public function __construct(OrganizationFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(): Seq
    {
        return $this->finder->find(['isEnabled' => true], ['all' => true, 'sortBy' => 'id'])->list;
    }
}
