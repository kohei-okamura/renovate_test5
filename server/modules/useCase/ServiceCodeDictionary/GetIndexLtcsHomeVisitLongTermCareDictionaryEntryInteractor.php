<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;
use UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecUseCase;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ一覧取得ユースケース実装.
 */
final class GetIndexLtcsHomeVisitLongTermCareDictionaryEntryInteractor implements GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    use BuildFinderResultHolder;

    private OfficeRepository $officeRepository;
    private IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase;
    private FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase
     * @param \UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase
     */
    public function __construct(
        OfficeRepository $officeRepository,
        IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase,
        FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase
    ) {
        $this->officeRepository = $officeRepository;
        $this->identifyCalcSpecUseCase = $identifyCalcSpecUseCase;
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $filterParams): FinderResult
    {
        // DEV-4975 暫定対応: Repositoryで取得、存在しなかった場合は空のリストを返却.
        $office = $this->lookupOffice($context, (int)$filterParams['officeId']);
        if ($office === null) {
            return $this->buildFinderResult(Seq::empty(), [], 'id');
        }
        $calcSpec = $this->identifyCalcSpec($context, $office, $filterParams['isEffectiveOn']);

        $specifiedOfficeAdditionFilter = $calcSpec !== null ?
            ['specifiedOfficeAddition' => $calcSpec->specifiedOfficeAddition] : [];
        $category = Arr::exists($filterParams, 'category') ?
            ['category' => LtcsServiceCodeCategory::fromLtcsProjectServiceCategory($filterParams['category'])] :
            [];
        $timeframe = Arr::exists($filterParams, 'timeframe')
            ? ['timeframe' => $filterParams['timeframe']]
            : [];
        return $this->useCase->handle(
            $context,
            $specifiedOfficeAdditionFilter
            + ['providedIn' => $filterParams['isEffectiveOn']]
            + $category
            + $timeframe
            + $filterParams,
            [
                'all' => false,
                'itemsPerPage' => 10, // 検索結果は常に最大10件に制限する。
            ]
        );
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @return null|\Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $officeId): ?Office
    {
        return $this->officeRepository
            ->lookup($officeId)
            ->filter(fn (Office $x): bool => $x->organizationId === $context->organization->id)
            ->headOption()
            ->getOrElseValue(null);
    }

    /**
     * 介護保険サービス：訪問介護：算定情報を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $effectivatedOn
     * @return null|\Domain\Office\HomeVisitLongTermCareCalcSpec
     */
    private function identifyCalcSpec(Context $context, Office $office, Carbon $effectivatedOn): ?HomeVisitLongTermCareCalcSpec
    {
        return $this->identifyCalcSpecUseCase->handle(
            $context,
            $office,
            $effectivatedOn
        )->getOrElseValue(null);
    }
}
