<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsHomeHelpServiceChunk as Chunk;
use Domain\Billing\DwsHomeHelpServiceDuration;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Domain\User\UserDwsCalcSpec;
use Lib\Arrays;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\SetupException;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：サービス詳細一覧組み立てユースケース（居宅介護用）実装.
 */
final class BuildDwsHomeHelpServiceServiceDetailListInteractor implements BuildDwsHomeHelpServiceServiceDetailListUseCase
{
    private const STANDARD_SERVICE_MINUTES_OF_PHYSICAL_CARE = 180;
    private const STANDARD_SERVICE_MINUTES_OF_NON_PHYSICAL_CARE = 90;

    /** 福祉専門職員等連携加算の月間算定回数上限 */
    private const WELFARE_SPECIAL_LIST_COOPERATION_ADDITION_COUNT_FOR_MONTH = 3;

    /** 緊急時対応加算の月間算定回数上限 */
    private const EMERGENCY_ADDITION_COUNT_FOR_MONTH = 2;

    /**
     * {@link \UseCase\Billing\CreateDwsHomeHelpServiceServiceDetailListInteractor} constructor.
     *
     * @param \UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase $createChunkListUseCase
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder $dictionaryEntryFinder
     * @param \UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase $computeCovid19PandemicSpecialAdditionUseCase
     */
    public function __construct(
        private CreateDwsHomeHelpServiceChunkListUseCase $createChunkListUseCase,
        private DwsHomeHelpServiceDictionaryEntryFinder $dictionaryEntryFinder,
        private ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase $computeCovid19PandemicSpecialAdditionUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Carbon $providedIn,
        Option $specOption,
        Option $userSpec,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport,
        Option $previousReport
    ): Seq {
        $main = Seq::from(...$this->generateMain($context, $providedIn, $certification, $provisionReport, $previousReport));
        $mainScore = $main->map(fn (DwsBillingServiceDetail $x): int => $x->totalScore)->sum();
        $additions = $this->generateAdditions(
            $context,
            $specOption,
            $userSpec,
            $providedIn,
            $provisionReport,
            $mainScore
        );
        return Seq::from(...$main, ...$additions);
    }

    /**
     * 本体サービス分（＝加算以外）のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousReport
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMain(
        Context $context,
        Carbon $providedIn,
        DwsCertification $certification,
        DwsProvisionReport $report,
        Option $previousReport
    ): iterable {
        return $this->createChunkListUseCase
            ->handle($context, $certification, $report, $previousReport)
            ->flatMap(function (Chunk $chunk) use ($providedIn): iterable {
                return $chunk->getDurations()->flatMap(function ($durations) use ($providedIn, $chunk): iterable {
                    return $this->generateDetailsFromDurations($providedIn, $chunk, $durations);
                });
            })
            ->filter(fn (DwsBillingServiceDetail $x): bool => $x->providedOn->isSameMonth($report->providedIn));
    }

    /**
     * サービス単位および時間帯別提供情報を用いてサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]|iterable $durations
     * @return iterable
     */
    private function generateDetailsFromDurations(
        Carbon $providedIn,
        Chunk $chunk,
        iterable $durations
    ): iterable {
        $durationsSeq = Seq::from(...$durations);
        $durationsCount = $durationsSeq->size();
        if ($durationsCount <= 0) {
            throw new LogicException('時間帯別提供情報がありません.');
        } elseif ($durationsCount === 1) {
            return $this->generateSingle($providedIn, $chunk, $durationsSeq->head());
        } else {
            return $durationsSeq
                // 日付 or 提供者区分が異なる場合は別々に処理を行う
                ->groupBy(function (DwsHomeHelpServiceDuration $x): string {
                    return sprintf('%s::%d', $x->providedOn->toDateString(), $x->providerType->value());
                })
                ->values()
                ->flatMap(function (Seq $xs) use ($providedIn, $chunk): iterable {
                    return $xs->exists(fn (DwsHomeHelpServiceDuration $x): bool => $x->isSpanning)
                        ? $this->generateMain2ndDay($providedIn, $chunk, $xs)
                        : $this->generateMain1stDay($providedIn, $chunk, $xs);
                });
        }
    }

    /**
     * 1日目のサービス詳細生成処理.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMain1stDay(
        Carbon $providedIn,
        Chunk $chunk,
        Seq $durations
    ): iterable {
        /** @var \Domain\Billing\DwsHomeHelpServiceDuration $headDuration */
        $headDuration = $durations->head();
        $standardServiceMinutes = self::getStandardServiceMinutes($chunk);
        $totalMinutes = $durations->map(fn (DwsHomeHelpServiceDuration $x): int => $x->duration)->sum();
        $firstTimeframeMinutes = $headDuration->duration;

        if ($totalMinutes <= $standardServiceMinutes) {
            // 対象サービス提供年月日における時間帯別提供情報の時間数の合計が標準サービス提供時間以内
            // -> 合成サービスコード × 1
            return $this->generateComposed($providedIn, $chunk, $durations);
        } elseif ($firstTimeframeMinutes <= $standardServiceMinutes) {
            // 対象サービス提供年月日における最初の時間帯別提供情報の時間数が標準サービス提供時間以内
            // -> 合成サービスコード × 1 + 増分サービスコード × n
            return [
                ...$this->generateComposed(
                    $providedIn,
                    $chunk,
                    $this->takeDurations($durations, $standardServiceMinutes)
                ),
                ...$this->generateExtra(
                    $providedIn,
                    $chunk,
                    $this->dropDurations($durations, $standardServiceMinutes)
                ),
            ];
        } else {
            // 上記に該当しない = 対象サービス提供年月日における最初の時間帯別提供情報の時間数が x 分超
            // → 単独サービスコード × 1 + 増分サービスコード × n
            return [
                ...$this->generateSingle($providedIn, $chunk, $headDuration),
                ...$this->generateExtra($providedIn, $chunk, $durations->drop(1)),
            ];
        }
    }

    /**
     * 日跨ぎ増分を含む2日目のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateMain2ndDay(
        Carbon $providedIn,
        Chunk $chunk,
        Seq $durations
    ): iterable {
        $standardServiceMinutes = self::getStandardServiceMinutes($chunk);
        $spanningDurationMinutes = $durations
            ->filter(fn (DwsHomeHelpServiceDuration $x): bool => $x->isSpanning)
            ->map(fn (DwsHomeHelpServiceDuration $x): int => $x->spanningDuration)
            ->headOption()
            ->getOrElseValue(0);

        if ($spanningDurationMinutes < $standardServiceMinutes) {
            // 日跨ぎ時間数が標準サービス提供時間未満
            // -> 日跨ぎ増分サービスコード × 1 + 増分サービスコード × n
            $spanningExtraMinutes = $standardServiceMinutes - $spanningDurationMinutes;
            return [
                ...$this->generateExtra(
                    $providedIn,
                    $chunk,
                    $this->takeDurations($durations, $spanningExtraMinutes),
                    true
                ),
                ...$this->generateExtra(
                    $providedIn,
                    $chunk,
                    $this->dropDurations($durations, $spanningExtraMinutes),
                    false
                ),
            ];
        } else {
            // 日跨ぎ時間数が標準サービス提供時間以上
            // 増分サービスコード × n
            return $this->generateExtra($providedIn, $chunk, $durations);
        }
    }

    /**
     * 単独サービスコードを生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param \Domain\Billing\DwsHomeHelpServiceDuration $duration
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateSingle(
        Carbon $providedIn,
        Chunk $chunk,
        DwsHomeHelpServiceDuration $duration
    ): iterable {
        $entry = $this->identifyDictionaryEntry(
            $providedIn,
            Seq::from($duration),
            $chunk->isPlannedByNovice
        );
        yield DwsBillingServiceDetail::create([
            'userId' => $chunk->userId,
            'providedOn' => $duration->providedOn,
            'serviceCode' => $entry->serviceCode,
            'serviceCodeCategory' => $entry->category,
            'unitScore' => $entry->score,
            'isAddition' => false,
            'count' => 1,
            'totalScore' => $entry->score,
        ]);
    }

    /**
     * 合成サービスコードを生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateComposed(
        Carbon $providedIn,
        Chunk $chunk,
        Seq $durations
    ): iterable {
        $head = $durations->head();
        $entry = $this->identifyDictionaryEntry($providedIn, $durations, $chunk->isPlannedByNovice);
        yield DwsBillingServiceDetail::create([
            'userId' => $chunk->userId,
            'providedOn' => $head->providedOn,
            'serviceCode' => $entry->serviceCode,
            'serviceCodeCategory' => $entry->category,
            'unitScore' => $entry->score,
            'isAddition' => false,
            'count' => 1,
            'totalScore' => $entry->score,
        ]);
    }

    /**
     * 増分サービスコードを生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @param bool $isSpanning
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateExtra(
        Carbon $providedIn,
        Chunk $chunk,
        Seq $durations,
        bool $isSpanning = false
    ): iterable {
        foreach ($durations as $duration) {
            $entry = $this->identifyDictionaryEntry(
                $providedIn,
                Seq::from($duration),
                $chunk->isPlannedByNovice,
                !$isSpanning,
                $isSpanning
            );
            yield DwsBillingServiceDetail::create([
                'userId' => $chunk->userId,
                'providedOn' => $duration->providedOn,
                'serviceCode' => $entry->serviceCode,
                'serviceCodeCategory' => $entry->category,
                'unitScore' => $entry->score,
                'isAddition' => false,
                'count' => 1,
                'totalScore' => $entry->score,
            ]);
        }
    }

    /**
     * 時間帯別提供情報一覧の先頭から指定した分数の分だけ取得する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @param int $durationMinutes
     * @param int $consumedDurationMinutes
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq
     */
    private function takeDurations(
        Seq $durations,
        int $durationMinutes,
        int $consumedDurationMinutes = 0
    ): Seq {
        if ($durations->isEmpty() || $consumedDurationMinutes >= $durationMinutes) {
            return Seq::empty();
        }
        /** @var \Domain\Billing\DwsHomeHelpServiceDuration $head */
        $head = $durations->head();
        if ($head->duration + $consumedDurationMinutes > $durationMinutes) {
            $x = $head->copy([
                'duration' => $durationMinutes - $consumedDurationMinutes,
            ]);
            return Seq::from($x);
        } else {
            $xs = $this->takeDurations(
                $durations->drop(1),
                $durationMinutes,
                $consumedDurationMinutes + $head->duration
            );
            return Seq::from($head, ...$xs);
        }
    }

    /**
     * 時間帯別提供情報一覧の先頭から指定した分数の分だけ破棄した一覧を取得する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations
     * @param int $durationMinutes
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq
     */
    private function dropDurations(Seq $durations, int $durationMinutes): Seq
    {
        if ($durationMinutes === 0 || $durations->isEmpty()) {
            return $durations;
        }
        /** @var \Domain\Billing\DwsHomeHelpServiceDuration $head */
        $head = $durations->head();
        if ($head->duration > $durationMinutes) {
            $x = $head->copy([
                'duration' => $head->duration - $durationMinutes,
            ]);
            return Seq::from($x, ...$durations->drop(1));
        } else {
            return $this->dropDurations($durations->drop(1), $durationMinutes - $head->duration);
        }
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
     * @param \Domain\Office\HomeHelpServiceCalcSpec[]&\ScalikePHP\Option $specOption
     * @param \Domain\User\UserDwsCalcSpec[]&\ScalikePHP\Option $userSpecOption
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $mainScore
     * @return iterable
     */
    private function generateAdditions(
        Context $context,
        Option $specOption,
        Option $userSpecOption,
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        int $mainScore
    ): iterable {
        $results = Seq::fromArray($provisionReport->results)
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->isHomeHelpService());
        if ($results->isEmpty()) {
            return [];
        }

        // この加算は別のユースケースに切り出しているので加算ごとにDBへの取得処理が発生しないように辞書エントリを渡すようにしている。
        // 理想としては請求単位でキャッシュしておいてそれをとってくるようにすれば渡さなくてよくなるのでそうしたい。DEV-5371
        $covid19PandemicSpecialAddition = $this->computeCovid19PandemicSpecialAdditionUseCase->handle(
            $context,
            $provisionReport,
            $mainScore,
            $this->identifyDictionaryEntryByCategoryOption(
                $providedIn,
                DwsServiceCodeCategory::covid19PandemicSpecialAddition()
            )
        );
        $covid19PandemicSpecialAdditionScore = $covid19PandemicSpecialAddition
            ->map(fn (DwsBillingServiceDetail $x): int => $x->totalScore)
            ->sum();

        // 特定事業所加算、特別地域加算、同一建物減算、処遇改善加算、処遇改善特別加算、特定処遇改善加算の計算対象には「新型コロナ感染症対応に係る加算」を含める
        $baseScore = $mainScore
            + $covid19PandemicSpecialAdditionScore;

        return [
            // 令和3年9月30日までの上乗せ分
            ...$covid19PandemicSpecialAddition,
            // 特定事業所加算
            ...$this->generateSpecifiedOfficeAddition($providedIn, $specOption, $provisionReport, $baseScore),
            // 緊急時対応加算
            ...$this->generateEmergencyAddition($providedIn, $provisionReport, $results),
            // 特別地域加算
            ...$this->generateSpecifiedAreaAddition($providedIn, $userSpecOption, $provisionReport, $baseScore),
            // 喀痰吸引等支援体制加算
            ...$this->generateSuckingAdditions($providedIn, $specOption, $provisionReport, $results),
            // 初回加算
            ...$this->generateFirstTimeAddition($providedIn, $provisionReport, $results),
            // 福祉専門職員等連携加算
            ...$this->generateWelfareSpecialistCooperationAddition($providedIn, $provisionReport, $results),
        ];
    }

    /**
     * 加算を表すサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\Common\Carbon $provideOn
     * @param null|int $score
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    private function generateAddition(
        Carbon $providedIn,
        DwsProvisionReport $report,
        DwsServiceCodeCategory $category,
        Carbon $provideOn,
        ?int $score = null
    ): DwsBillingServiceDetail {
        $entry = $this->identifyDictionaryEntryByCategory($providedIn, $category);
        return DwsBillingServiceDetail::create([
            'userId' => $report->userId,
            'providedOn' => $provideOn,
            'serviceCode' => $entry->serviceCode,
            'serviceCodeCategory' => $entry->category,
            'unitScore' => $score ?? $entry->score,
            'isAddition' => true,
            'count' => 1,
            'totalScore' => $score ?? $entry->score,
        ]);
    }

    /**
     * 初回加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateFirstTimeAddition(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): iterable {
        $option = ServiceOption::firstTime();
        return $results
            ->find(fn (DwsProvisionReportItem $x): bool => $x->hasOption($option))
            ->map(function (DwsProvisionReportItem $x) use ($providedIn, $provisionReport): DwsBillingServiceDetail {
                $category = DwsServiceCodeCategory::firstTimeAddition();
                return $this->generateAddition($providedIn, $provisionReport, $category, $x->schedule->date);
            });
    }

    /**
     * 喀痰吸引等支援体制加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\HomeHelpServiceCalcSpec[]&\ScalikePHP\Option $specOption
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateSuckingAdditions(
        Carbon $providedIn,
        Option $specOption,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): iterable {
        foreach ($specOption as $spec) {
            assert($spec instanceof HomeHelpServiceCalcSpec);
            if ($spec->specifiedOfficeAddition !== HomeHelpServiceSpecifiedOfficeAddition::addition1()) {
                $option = ServiceOption::sucking();
                return $results
                    ->find(fn (DwsProvisionReportItem $x): bool => $x->hasOption($option))
                    ->map(function (DwsProvisionReportItem $x) use ($providedIn, $provisionReport): DwsBillingServiceDetail {
                        $category = DwsServiceCodeCategory::suckingSupportSystemAddition();
                        return $this->generateAddition($providedIn, $provisionReport, $category, $x->schedule->date);
                    });
            }
        }
        return Seq::empty();
    }

    /**
     * 特定事業所加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Office\HomeHelpServiceCalcSpec[]&\ScalikePHP\Option $specOption
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateSpecifiedOfficeAddition(
        Carbon $providedIn,
        Option $specOption,
        DwsProvisionReport $provisionReport,
        int $baseScore
    ): iterable {
        $ret = Seq::empty();
        foreach ($specOption as $spec) {
            assert($spec instanceof HomeHelpServiceCalcSpec);
            $ret = $spec->specifiedOfficeAddition
                ->compute($baseScore, $provisionReport->providedIn)
                ->flatMap(function (int $score) use ($providedIn, $spec, $provisionReport): Option {
                    $categoryOption = DwsServiceCodeCategory::fromHomeHelpServiceSpecifiedOfficeAddition(
                        $spec->specifiedOfficeAddition
                    );
                    return $categoryOption->map(
                        fn (DwsServiceCodeCategory $category): DwsBillingServiceDetail => $this->generateAddition(
                            $providedIn,
                            $provisionReport,
                            $category,
                            $provisionReport->providedIn->endOfMonth()->startOfDay(),
                            $score
                        )
                    );
                });
        }
        return $ret;
    }

    /**
     * 緊急時対応加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateEmergencyAddition(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): iterable {
        // 算定は2回/月まで
        return $results
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->hasOption(ServiceOption::emergency()))
            ->take(self::EMERGENCY_ADDITION_COUNT_FOR_MONTH)
            ->map(fn (DwsProvisionReportItem $item): DwsBillingServiceDetail => $this->generateAddition(
                $providedIn,
                $provisionReport,
                DwsServiceCodeCategory::emergencyAddition1(),
                $item->schedule->date
            ));
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
                ->map(fn (int $score): DwsBillingServiceDetail => $this->generateAddition(
                    $providedIn,
                    $provisionReport,
                    DwsServiceCodeCategory::specifiedAreaAddition(),
                    $dayOfEndOfMonth,
                    $score
                ))
        );
    }

    /**
     * 福祉専門職員等連携加算のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $results
     * @return \Domain\Billing\DwsBillingServiceDetail[]&iterable
     */
    private function generateWelfareSpecialistCooperationAddition(
        Carbon $providedIn,
        DwsProvisionReport $provisionReport,
        Seq $results
    ): iterable {
        // 算定は3回/月まで。
        // 本来は90日間で3回、となっているが判定が難しいため運用でカバー。
        return $results
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->hasOption(
                ServiceOption::welfareSpecialistCooperation()
            ))
            ->take(self::WELFARE_SPECIAL_LIST_COOPERATION_ADDITION_COUNT_FOR_MONTH)
            ->map(fn (DwsProvisionReportItem $item): DwsBillingServiceDetail => $this->generateAddition(
                $providedIn,
                $provisionReport,
                DwsServiceCodeCategory::welfareSpecialistCooperationAddition(),
                $item->schedule->date
            ));
    }

    /**
     * 標準サービス提供時間を取得する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @return int
     */
    private static function getStandardServiceMinutes(Chunk $chunk): int
    {
        return match ($chunk->category) {
            DwsServiceCodeCategory::physicalCare(),
            DwsServiceCodeCategory::accompanyWithPhysicalCare() => self::STANDARD_SERVICE_MINUTES_OF_PHYSICAL_CARE,
            DwsServiceCodeCategory::housework(),
            DwsServiceCodeCategory::accompany() => self::STANDARD_SERVICE_MINUTES_OF_NON_PHYSICAL_CARE,
            default => throw new LogicException("Unexpected category: {$chunk->category->value()}"),
        };
    }

    /**
     * サービスコード辞書エントリを特定する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsHomeHelpServiceDuration[]&\ScalikePHP\Seq $durations 時間帯別提供情報
     * @param bool $isPlannedByNovice 初計フラグ
     * @param bool $isExtra 増分フラグ
     * @param bool $isSpanning 日跨ぎフラグ
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry 辞書エントリ
     */
    private function identifyDictionaryEntry(
        Carbon $providedIn,
        Seq $durations,
        bool $isPlannedByNovice,
        bool $isExtra = false,
        bool $isSpanning = false
    ): DwsHomeHelpServiceDictionaryEntry {
        /** @var \Domain\Billing\DwsHomeHelpServiceDuration $headDuration */
        $headDuration = $durations->head();
        $durationParams = Arrays::generate(function () use ($durations, $isSpanning): iterable {
            foreach ($durations as $duration) {
                switch ($duration->timeframe) {
                    case Timeframe::midnight():
                        if ($isSpanning) {
                            // 日跨ぎ時なので深夜1に1日目の時間数、深夜2に残りの時間数
                            yield 'midnightDuration1' => $duration->spanningDuration;
                            yield 'midnightDuration2' => $duration->duration;
                        } else {
                            yield 'midnightDuration1' => $duration->duration;
                        }
                        break;
                    case Timeframe::morning():
                        yield 'morningDuration' => $duration->duration;
                        break;
                    case Timeframe::daytime():
                        yield 'daytimeDuration' => $duration->duration;
                        break;
                    case Timeframe::night():
                        yield 'nightDuration' => $duration->duration;
                        break;
                    default:
                }
            }
        });
        $filterParams = [
            'providedIn' => $providedIn,
            'category' => $headDuration->category,
            'isSecondary' => $headDuration->isSecondary,
            'isExtra' => $isExtra,
            'isPlannedByNovice' => $isPlannedByNovice,
            'providerType' => $headDuration->providerType,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ];
        return $this->dictionaryEntryFinder
            ->find($durationParams + $filterParams, $paginationParams)
            ->list
            ->headOption()
            ->getOrElse(function () use ($durationParams, $filterParams): void {
                $data = Json::encode($durationParams + $filterParams);
                throw new SetupException("ServiceCode is not found: {$data}");
            });
    }

    /**
     * サービスコード区分からサービスコード辞書エントリを特定する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary $dictionary
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param Carbon $providedIn
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]&\ScalikePHP\Option
     */
    private function identifyDictionaryEntryByCategoryOption(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): Option {
        $filterParams = [
            'category' => $category,
            'providedIn' => $providedIn,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ];
        return $this->dictionaryEntryFinder
            ->find($filterParams, $paginationParams)
            ->list
            ->headOption();
    }

    /**
     * サービスコード区分からサービスコード辞書エントリを特定する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry
     */
    private function identifyDictionaryEntryByCategory(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): DwsHomeHelpServiceDictionaryEntry {
        return $this
            ->identifyDictionaryEntryByCategoryOption($providedIn, $category)
            ->getOrElse(function () use ($providedIn, $category): void {
                $data = Json::encode([
                    'category' => $category,
                    'providedIn' => $providedIn,
                ]);
                throw new SetupException("ServiceCode is not found: {$data}");
            });
    }
}
