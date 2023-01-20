<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;

/**
 * DwsVisitingCareForPwsdDictionaryEntry Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsVisitingCareForPwsdDictionaryEntryFixture
{
    /**
     * サービスコード辞書エントリ（障害：重度訪問介護） 登録.
     */
    protected function createDwsVisitingCareForPwsdDictionaryEntries(): void
    {
        foreach ($this->examples->dwsVisitingCareForPwsdDictionaryEntries as $entity) {
            $x = DwsVisitingCareForPwsdDictionaryEntry::fromDomain($entity);
            $x->save();
        }
    }
}
