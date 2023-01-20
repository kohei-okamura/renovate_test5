<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use UseCase\FindInteractorFeature;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ検索ユースケース実装.
 */
final class FindLtcsHomeVisitLongTermCareDictionaryEntryInteractor implements FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder $finder
     */
    public function __construct(LtcsHomeVisitLongTermCareDictionaryEntryFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'serviceCode';
    }
}
