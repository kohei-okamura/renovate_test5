<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * DwsVisitingCareForPwsdDictionary Repository Interface.
 */
interface DwsVisitingCareForPwsdDictionaryRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary $entity
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
