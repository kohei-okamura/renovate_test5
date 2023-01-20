<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;
use UseCase\BuildFinderResultHolder;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ取得ユースケース実装.
 */
final class GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor implements GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    use BuildFinderResultHolder;

    private FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $findDictionaryEntryUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $findDictionaryEntryUseCase
     */
    public function __construct(
        FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $findDictionaryEntryUseCase
    ) {
        $this->findDictionaryEntryUseCase = $findDictionaryEntryUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        string $serviceCode,
        Carbon $providedIn
    ): Option {
        return $this->findDictionaryEntryUseCase->handle(
            $context,
            ['q' => $serviceCode, 'providedIn' => $providedIn],
            ['all' => true]
        )
            ->list
            ->headOption();
    }
}
