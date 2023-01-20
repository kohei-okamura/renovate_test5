<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingServiceDetail as ServiceDetail;
use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Billing\DwsVisitingCareForPwsdDuration as Duration;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DictionaryEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Domain\User\UserDwsCalcSpec;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;
use ScalikePHP\ScalikeTraversable;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス詳細一覧組み立てユースケース（重度訪問介護用）実装.
 */
final class BuildDwsVisitingCareForPwsdServiceDetailListInteractor implements BuildDwsVisitingCareForPwsdServiceDetailListUseCase
{
    // 1日の最低サービス提供単位（概ね30分以上の場合は1時間として算定可能）
    private const MIN_DURATION_PER_DAY = 30;

    // 移動介護加算算定可能時間上限
    // 移動介護加算は3時間を越えると打ち止め（移動介護加算4.0）となるため
    // 既に算定済みの移動介護加算の時間数が3時間以内の場合のみ算定可能
    private const MAX_MOVING_DURATION_MINUTES_PER_DAY = 180;

    // ちゃんとLaravelのキャッシュを使ったものにしたほうが良い気がするが、後回し.非同期前提なので大丈夫なはず。
    /** 辞書キャッシュ */
    /** @var array <int => array> key=dictionaryId, value=dwsVisitingCareForPwsdDictionaryEntries */
    private array $entries;

    /**
     * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListInteractor} constructor.
     *
     * @param \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase $createChunkListUseCase
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder $dictionaryEntryFinder
     * @param \UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase $computeCovid19PandemicSpecialAdditionUseCase
     */
    public function __construct(
        private CreateDwsVisitingCareForPwsdChunkListUseCase $createChunkListUseCase,
        private DwsVisitingCareForPwsdDictionaryEntryFinder $dictionaryEntryFinder,
        private ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase $computeCovid19PandemicSpecialAdditionUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Carbon $providedIn,
        Option $spec,
        Option $userSpec,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport
    ): Seq {
        $xs = Seq::from(...$this->generateMain($context, $certification, $providedIn, $provisionReport));
        [$movingAddition, $main] = $xs->partition(fn (ServiceDetail $x): bool => $x->isAddition);
        $additions = $this->generateAdditions(
            $context,
            $spec,
            $userSpec,
            $providedIn,
            $provisionReport,
            $main->map(fn (ServiceDetail $x): int => $x->totalScore)->sum(),
            $movingAddition->map(fn (ServiceDetail $x): int => $x->totalScore)->sum()
        );
        return Seq::from(...$main, ...$movingAddition, ...$additions);
    }

    /**
     * 本体サービス分（＝加算以外）のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMain(
        Context $context,
        DwsCertification $certification,
        Carbon $providedIn,
        DwsProvisionReport $provisionReport
    ): iterable {
        $chunks = $this->createChunkListUseCase->handle($context, $certification, $provisionReport);
        foreach ($chunks as $chunk) {
            yield from $this->generateByChunk($chunk, $providedIn);
        }
    }

    /**
     * サービス単位および時間帯別提供情報を用いてサービス詳細を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateByChunk(Chunk $chunk, Carbon $providedIn): iterable
    {
        foreach ($chunk->getDurations() as $durations) {
            if (empty($durations)) {
                throw new LogicException('Invalid durations array: it is empty');
            }

            $xs = Seq::fromArray($durations);
            [$movingDurations, $nonMovingDurations] = $xs->partition(fn (Duration $x): bool => $x->isMoving);

            $totalDurationMinutes = $nonMovingDurations->map(fn (Duration $x): int => $x->duration)->sum();
            if ($totalDurationMinutes >= self::MIN_DURATION_PER_DAY) {
                yield from $this->generateNonMoving($chunk, $providedIn, $nonMovingDurations);
                yield from $this->generateMoving($chunk, $providedIn, $movingDurations);
            }
        }
    }

    /**
     * 時間帯別提供情報の Seq に対応する移動加算以外のサービス詳細一覧を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration[]&\ScalikePHP\ScalikeTraversable $durations
     * @param int $offsetMinutes
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateNonMoving(
        Chunk $chunk,
        Carbon $providedIn,
        ScalikeTraversable $durations,
        int $offsetMinutes = 0
    ): iterable {
        if ($durations->nonEmpty()) {
            /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $head */
            $head = $durations->head();
            $tail = $durations->tail();
            $entries = $this->findDictionaryEntriesByDuration($providedIn, $head);
            yield from $this->generateNonMovingByDuration(
                $chunk,
                $head,
                $entries,
                $head->isSecondary,
                $offsetMinutes
            );
            yield from $this->generateNonMoving($chunk, $providedIn, $tail, $offsetMinutes + $head->duration);
        }
    }

    /**
     * 単一の時間帯別提供情報に対応する移動加算以外のサービス詳細一覧を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration $duration
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]&\ScalikePHP\Seq $entries
     * @param bool $isSecondary
     * @param int $offsetMinutes 既に他の Duration に対して計算済みの時間量（分）
     * @param int $consumedMinutes 現在の Duration に対して計算済みの時間量（分）
     * @param null&\Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option $currentOrNull
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateNonMovingByDuration(
        Chunk $chunk,
        Duration $duration,
        Seq $entries,
        bool $isSecondary,
        int $offsetMinutes,
        int $consumedMinutes = 0,
        ?Option $currentOrNull = null
    ): iterable {
        $current = Option::from($currentOrNull)->flatten();
        if ($consumedMinutes >= $duration->duration) {
            return $current;
        } else {
            $entry = $this->identifyDictionaryEntry($entries, $isSecondary, $offsetMinutes + $consumedMinutes);
            $mergeToCurrent = $current->exists(function (ServiceDetail $x) use ($chunk, $entry): bool {
                return $x->userId === $chunk->userId
                    && $x->providedOn->eq($chunk->providedOn)
                    && $x->serviceCode->equals($entry->serviceCode);
            });
            $newCurrent = $mergeToCurrent
                ? $current->map(fn (ServiceDetail $x): ServiceDetail => $x->copy([
                    'count' => $x->count + 1,
                    'totalScore' => $x->unitScore * ($x->count + 1),
                ]))
                : Option::some($this->createServiceDetailFromChunk($entry, $chunk));
            $xs = $this->generateNonMovingByDuration(
                $chunk,
                $duration,
                $entries,
                $isSecondary,
                $offsetMinutes,
                $consumedMinutes + $entry->unit,
                $newCurrent
            );
            return $mergeToCurrent ? $xs : [...$current, ...$xs];
        }
    }

    /**
     * 時間帯別提供情報の Seq に対応する移動加算のサービス詳細一覧を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration[]&\ScalikePHP\ScalikeTraversable $durations
     * @param int $offsetMinutes
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMoving(
        Chunk $chunk,
        Carbon $providedIn,
        ScalikeTraversable $durations,
        int $offsetMinutes = 0
    ): iterable {
        if ($durations->nonEmpty()) {
            /** @var \Domain\Billing\DwsVisitingCareForPwsdDuration $head */
            $head = $durations->head();
            $tail = $durations->tail();
            $entries = $this->findDictionaryEntriesByDuration($providedIn, $head);
            yield from $this->generateMovingByDuration($chunk, $head, $entries, $head->isSecondary, $offsetMinutes);
            yield from $this->generateMoving($chunk, $providedIn, $tail, $offsetMinutes + $head->duration);
        }
    }

    /**
     * 単一の時間帯別提供情報に対応する移動加算のサービス詳細一覧を生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration $duration
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]&\ScalikePHP\Seq $entries
     * @param bool $isSecondary
     * @param int $offsetMinutes 既に他の Duration に対して計算済みの時間量（分）
     * @param int $consumedMinutes 現在の Duration に対して計算済みの時間量（分）
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMovingByDuration(
        Chunk $chunk,
        Duration $duration,
        Seq $entries,
        bool $isSecondary,
        int $offsetMinutes,
        int $consumedMinutes = 0
    ): iterable {
        $isAbleToGenerate = $consumedMinutes < $duration->duration
            && $offsetMinutes + $consumedMinutes <= self::MAX_MOVING_DURATION_MINUTES_PER_DAY;
        if ($isAbleToGenerate) {
            $entry = $this->identifyDictionaryEntry($entries, $isSecondary, $offsetMinutes + $consumedMinutes);
            yield $this->createServiceDetailFromChunk($entry, $chunk, true);
            yield from $this->generateMovingByDuration(
                $chunk,
                $duration,
                $entries,
                $isSecondary,
                $offsetMinutes,
                $consumedMinutes + $entry->unit
            );
        }
    }

    /**
     * サービス詳細を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $entry
     * @param int $userId
     * @param \Domain\Common\Carbon $providedOn
     * @param bool $isAddition
     * @param null&int $score
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    private function createServiceDetail(
        DictionaryEntry $entry,
        int $userId,
        Carbon $providedOn,
        bool $isAddition = false,
        ?int $score = null
    ): ServiceDetail {
        return ServiceDetail::create([
            'userId' => $userId,
            'providedOn' => $providedOn,
            'serviceCode' => $entry->serviceCode,
            'serviceCodeCategory' => $entry->category,
            'unitScore' => $score ?? $entry->score,
            'isAddition' => $isAddition,
            'count' => 1,
            'totalScore' => $score ?? $entry->score,
        ]);
    }

    /**
     * サービス単位からサービス詳細を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $entry
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @param bool $isAddition
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    private function createServiceDetailFromChunk(
        DictionaryEntry $entry,
        Chunk $chunk,
        bool $isAddition = false
    ): ServiceDetail {
        return $this->createServiceDetail($entry, $chunk->userId, $chunk->providedOn, $isAddition);
    }

    /**
     * 実績ごとにサービス詳細を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $entry
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    private function createServiceDetailForResults(
        DictionaryEntry $entry,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): Seq {
        return $results->map(function (DwsProvisionReportItem $x) use ($entry, $provisionReport): ServiceDetail {
            return $this->createServiceDetail($entry, $provisionReport->userId, $x->schedule->date);
        });
    }

    /**
     * サービスコード辞書エントリ（障害：重度訪問介護）の一覧を取得する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsVisitingCareForPwsdDuration $duration
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]&\ScalikePHP\Seq
     */
    private function findDictionaryEntriesByDuration(Carbon $providedIn, Duration $duration): Seq
    {
        // TODO: DEV-4811 サービスコード辞書エントリ（障害：重度訪問介護）一覧取得処理にキャッシュ処理を追加する
        $filterParams = $duration->isMoving
            ? [
                'providedIn' => $providedIn,
                'category' => DwsServiceCodeCategory::outingSupportForPwsd(),
                'isCoaching' => $duration->isCoaching,
                'timeframe' => Timeframe::unknown(),
            ]
            : [
                'providedIn' => $providedIn,
                'category' => $duration->category,
                'isCoaching' => $duration->isCoaching,
                'isHospitalized' => $duration->isHospitalized,
                'isLongHospitalized' => $duration->isLongHospitalized,
                'timeframe' => $duration->timeframe,
            ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->dictionaryEntryFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * サービスコード辞書エントリ（障害：重度訪問介護）の一覧から対象となるエントリを特定する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]&\ScalikePHP\Seq $entries
     * @param bool $isSecondary
     * @param int $durationMinutes
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    private function identifyDictionaryEntry(Seq $entries, bool $isSecondary, int $durationMinutes): DictionaryEntry
    {
        // 最初の1時間のみ最低60分・最大60分で定義されているため異なる条件を用いる
        $entryOption = $durationMinutes === 0
            ? $entries->find(function (DictionaryEntry $x) use ($isSecondary): bool {
                return $x->duration->start === $x->duration->end
                    && $x->isSecondary === $isSecondary;
            })
            : $entries->find(function (DictionaryEntry $x) use ($isSecondary, $durationMinutes): bool {
                return $x->duration->start <= $durationMinutes
                    && $x->duration->end > $durationMinutes
                    && $x->isSecondary === $isSecondary;
            });
        return $entryOption->getOrElse(function () use ($isSecondary, $durationMinutes): void {
            throw new SetupException("DwsVisitingCareForPwsdDictionaryEntry not found. isSecondary={$isSecondary}, durationMinutes={$durationMinutes}");
        });
    }

    /**
     * 各種加算のサービス詳細一覧を生成する.
     *
     * 下記の加算は上限管理を行わない場合（＝加算が算定できない）場合に対応するため明細書で算出する.
     *
     * - 利用者負担上限額管理加算
     * - 福祉・介護職員処遇改善加算
     * - 福祉・介護職員特定処遇改善加算
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec&\ScalikePHP\Option $specOption
     * @param \Domain\User\UserDwsCalcSpec&\ScalikePHP\Option $userSpecOption
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $mainScore
     * @param int $movingAdditionScore
     * @return iterable
     */
    private function generateAdditions(
        Context $context,
        Option $specOption,
        Option $userSpecOption,
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        int $mainScore,
        int $movingAdditionScore
    ): iterable {
        $results = Seq::fromArray($provisionReport->results)
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->isVisitingCareForPwsd());
        if ($results->isEmpty()) {
            return [];
        }

        $covid19Addition = $this->generateCovid19PandemicSpecialAddition(
            $context,
            $providedIn,
            $provisionReport,
            $mainScore
        );
        $covid19AdditionScore = $covid19Addition->map(fn (ServiceDetail $x): int => $x->totalScore)->sum();

        // 特定事業所加算の計算対象には移動加算および「令和3年9月30日までの上乗せ分」を含める
        $baseScore = $mainScore + $covid19AdditionScore;

        // TODO: 加算はユースケースごとにいつか分離したい。
        return [
            // 令和3年9月30日までの上乗せ分
            ...$covid19Addition,
            // 特定事業所加算
            ...$this->generateSpecifiedOfficeAddition($providedIn, $specOption, $provisionReport, $baseScore),
            // 緊急時対応加算
            ...$this->generateEmergencyAdditions($providedIn, $provisionReport, $results),
            // 特別地域加算
            ...$this->generateSpecifiedAreaAddition($providedIn, $userSpecOption, $provisionReport, $baseScore),
            // 喀痰吸引等支援体制加算
            ...$this->generateSuckingAdditions($providedIn, $specOption, $provisionReport, $results),
            // 初回加算
            ...$this->generateFirstTimeAddition($providedIn, $provisionReport, $results),
            //  行動障害支援連携加算
            ...$this->generateBehavioralDisorderSupportCooperationAddition($providedIn, $provisionReport, $results),
        ];
    }

    /**
     * 令和3年9月30日までの上乗せ分のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateCovid19PandemicSpecialAddition(
        Context $context,
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        int $baseScore
    ): Option {
        $seq = $this->computeCovid19PandemicSpecialAdditionUseCase->handle(
            $context,
            $provisionReport,
            $baseScore,
            $this->dictionaryEntryFinder->findByCategoryOption(
                $providedIn,
                DwsServiceCodeCategory::covid19PandemicSpecialAddition()
            )
        );
        return $seq->headOption();
    }

    /**
     * 特定事業所加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec&\ScalikePHP\Option $specOption
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateSpecifiedOfficeAddition(
        Carbon $providedIn,
        Option $specOption,
        DwsProvisionReport $provisionReport,
        int $baseScore
    ): Option {
        foreach ($specOption as $spec) {
            assert($spec instanceof VisitingCareForPwsdCalcSpec);
            $categoryOption = DwsServiceCodeCategory::fromVisitingCareForPwsdSpecifiedOfficeAddition(
                $spec->specifiedOfficeAddition
            );
            return $categoryOption->map(
                function (DwsServiceCodeCategory $category) use (
                    $providedIn,
                    $spec,
                    $provisionReport,
                    $baseScore
                ): ServiceDetail {
                    $score = $spec->specifiedOfficeAddition
                        ->compute($baseScore, $provisionReport->providedIn)
                        ->getOrElse(function () use ($category): void {
                            throw new LogicException("Failed to compute score for VisitingCareForPwsdSpecifiedOfficeAddition({$category})");
                        });
                    $dayOfEndOfMonth = $provisionReport->providedIn->endOfMonth()->startOfDay();
                    return $this->generateForCategory(
                        $providedIn,
                        $provisionReport,
                        $category,
                        $dayOfEndOfMonth,
                        true,
                        $score
                    );
                }
            );
        }
        return Option::none();
    }

    /**
     * 緊急時対応加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    private function generateEmergencyAdditions(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): Seq {
        $xs = $results
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->hasOption(ServiceOption::emergency()))
            ->take(2);
        if ($xs->nonEmpty()) {
            $category = DwsServiceCodeCategory::emergencyAddition1();
            $entry = $this->dictionaryEntryFinder->findByCategory($providedIn, $category);
            return $this->createServiceDetailForResults($entry, $provisionReport, $xs);
        }
        return Seq::emptySeq();
    }

    /**
     * 特定事業所加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\User\UserDwsCalcSpec[]&\ScalikePHP\Option $userCalcSpecOption
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateSpecifiedAreaAddition(
        Carbon $providedIn,
        Option $userCalcSpecOption,
        DwsProvisionReport $provisionReport,
        int $baseScore
    ): Option {
        $dayOfEndOfMonth = $provisionReport->providedIn->endOfMonth()->startOfDay();
        return $userCalcSpecOption->flatMap(
            fn (UserDwsCalcSpec $userCalcSpec): Option => $userCalcSpec->locationAddition
                ->compute($baseScore)
                ->map(fn (int $score): DwsBillingServiceDetail => $this->generateForCategory(
                    $providedIn,
                    $provisionReport,
                    DwsServiceCodeCategory::specifiedAreaAddition(),
                    $dayOfEndOfMonth,
                    true,
                    $score
                ))
        );
    }

    /**
     * 喀痰吸引等支援体制加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec&\ScalikePHP\Option $specOption
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    private function generateSuckingAdditions(
        Carbon $providedIn,
        Option $specOption,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): Seq {
        foreach ($specOption as $spec) {
            assert($spec instanceof VisitingCareForPwsdCalcSpec);
            if ($spec->specifiedOfficeAddition !== VisitingCareForPwsdSpecifiedOfficeAddition::addition1()) {
                $xs = $results
                    ->filter(fn (DwsProvisionReportItem $x): bool => $x->hasOption(ServiceOption::sucking()))
                    ->distinctBy(fn (DwsProvisionReportItem $x): string => $x->schedule->date->toDateString());
                if ($results->nonEmpty()) {
                    $category = DwsServiceCodeCategory::suckingSupportSystemAddition();
                    $entry = $this->dictionaryEntryFinder->findByCategory($providedIn, $category);
                    return $this->createServiceDetailForResults($entry, $provisionReport, $xs);
                }
            }
        }
        return Seq::empty();
    }

    /**
     * 初回加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateFirstTimeAddition(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): Option {
        $option = ServiceOption::firstTime();
        return $results
            ->find(fn (DwsProvisionReportItem $x): bool => $x->hasOption($option))
            ->map(function (DwsProvisionReportItem $x) use ($providedIn, $provisionReport): ServiceDetail {
                $category = DwsServiceCodeCategory::firstTimeAddition();
                return $this->generateForCategory($providedIn, $provisionReport, $category, $x->schedule->date, true);
            });
    }

    /**
     * 行動障害支援連携加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateBehavioralDisorderSupportCooperationAddition(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): Option {
        $option = ServiceOption::behavioralDisorderSupportCooperation();
        return $results
            ->find(fn (DwsProvisionReportItem $x): bool => $x->hasOption($option))
            ->map(function (DwsProvisionReportItem $x) use ($providedIn, $provisionReport): ServiceDetail {
                $category = DwsServiceCodeCategory::behavioralDisorderSupportCooperationAddition();
                return $this->generateForCategory($providedIn, $provisionReport, $category, $x->schedule->date, true);
            });
    }

    /**
     * サービスコード区分を指定してサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\Common\Carbon $providedOn
     * @param bool $isAddition
     * @param null|int $score
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    private function generateForCategory(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        DwsServiceCodeCategory $category,
        Carbon $providedOn,
        bool $isAddition = false,
        ?int $score = null
    ): ServiceDetail {
        $entry = $this->dictionaryEntryFinder->findByCategory($providedIn, $category);
        return $this->createServiceDetail($entry, $provisionReport->userId, $providedOn, $isAddition, $score);
    }
}
