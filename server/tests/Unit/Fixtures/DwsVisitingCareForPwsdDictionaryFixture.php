<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;

/**
 * DwsVisitingCareForPwsdDictionary fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsVisitingCareForPwsdDictionaryFixture
{
    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書 登録.
     *
     * @return void
     */
    protected function createDwsVisitingCareForPwsdDictionaries(): void
    {
        foreach ($this->examples->dwsVisitingCareForPwsdDictionaries as $entity) {
            $x = DwsVisitingCareForPwsdDictionary::fromDomain($entity);
            $x->save();
        }
    }
}
