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
 * サービスコード辞書エントリ（障害：重度訪問介護）リポジトリ.
 */
interface DwsVisitingCareForPwsdDictionaryEntryRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $entity
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
