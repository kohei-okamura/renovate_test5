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
 * DwsHomeHelpServiceDictionary Repository Interface.
 */
interface DwsHomeHelpServiceDictionaryRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary $entity
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
