<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry} Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsHomeVisitLongTermCareDictionaryEntryFixture
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリをデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsHomeVisitLongTermCareDictionaryEntries(): void
    {
        foreach ($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries as $entity) {
            $x = LtcsHomeVisitLongTermCareDictionaryEntry::fromDomain($entity);
            $x->save();
        }
    }
}
