<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書リポジトリ.
 */
interface LtcsHomeVisitLongTermCareDictionaryRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $entity
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
