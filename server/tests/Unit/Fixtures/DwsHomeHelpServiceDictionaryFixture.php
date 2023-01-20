<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;

/**
 * DwsHomeHelpServiceDictionary fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsHomeHelpServiceDictionaryFixture
{
    /**
     * 障害福祉サービス：居宅介護：サービスコード辞書 登録.
     *
     * @return void
     */
    protected function createDwsHomeHelpServiceDictionaries(): void
    {
        foreach ($this->examples->dwsHomeHelpServiceDictionaries as $entity) {
            $x = DwsHomeHelpServiceDictionary::fromDomain($entity);
            $x->save();
        }
    }
}
