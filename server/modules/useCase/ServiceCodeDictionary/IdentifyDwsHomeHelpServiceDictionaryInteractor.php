<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinder;
use ScalikePHP\Option;

/**
 * 居宅介護サービスコード辞書特定 ユースケース実装.
 */
class IdentifyDwsHomeHelpServiceDictionaryInteractor implements IdentifyDwsHomeHelpServiceDictionaryUseCase
{
    private DwsHomeHelpServiceDictionaryFinder $homeHelpServiceDictionaryFinder;

    /**
     * constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinder $homeHelpServiceDictionaryFinder
     */
    public function __construct(DwsHomeHelpServiceDictionaryFinder $homeHelpServiceDictionaryFinder)
    {
        $this->homeHelpServiceDictionaryFinder = $homeHelpServiceDictionaryFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Carbon $targetDate): Option
    {
        return $this->homeHelpServiceDictionaryFinder
            ->find(['effectivatedBefore' => $targetDate], ['itemsPerPage' => 1, 'sortBy' => 'id', 'desc' => true])
            ->list
            ->headOption();
    }
}
