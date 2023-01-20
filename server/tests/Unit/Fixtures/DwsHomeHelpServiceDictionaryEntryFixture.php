<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry} Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsHomeHelpServiceDictionaryEntryFixture
{
    /**
     * サービスコード辞書エントリ（障害：居宅介護）をデータベースに登録する.
     */
    protected function createDwsHomeHelpServiceDictionaryEntries(): void
    {
        foreach ($this->examples->dwsHomeHelpServiceDictionaryEntries as $entity) {
            DwsHomeHelpServiceDictionaryEntry::fromDomain($entity)->save();
        }
    }
}
