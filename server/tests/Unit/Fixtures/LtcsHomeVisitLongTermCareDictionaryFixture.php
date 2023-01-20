<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary} Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsHomeVisitLongTermCareDictionaryFixture
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書をデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsHomeVisitLongTermCareDictionaries(): void
    {
        foreach ($this->examples->ltcsHomeVisitLongTermCareDictionaries as $entity) {
            $x = LtcsHomeVisitLongTermCareDictionary::fromDomain($entity);
            $x->save();
        }
    }
}
