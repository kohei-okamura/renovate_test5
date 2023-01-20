<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Finder;
use ScalikePHP\Option;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry} Finder Interface.
 */
interface DwsHomeHelpServiceDictionaryEntryFinder extends Finder
{
    // TODO: DEV-4855 障害福祉サービスの各種辞書エントリ Finder に実装したカテゴリ検索専用メソッドに関する設計について再検討

    /**
     * サービスコード区分を指定して辞書エントリを検索する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry
     */
    public function findByCategory(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): DwsHomeHelpServiceDictionaryEntry;

    /**
     * サービスコード区分を指定して辞書エントリを検索する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \ScalikePHP\Option
     */
    public function findByCategoryOption(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): Option;
}
