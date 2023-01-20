<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Model;
use Domain\Office\Office;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use Lib\Math;
use Lib\Strings;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票PDF.
 *
 * @property-read \Domain\LtcsInsCard\LtcsInsCardStatus $status 介護保険認定区分
 * @property-read string $providedIn サービス提供年月
 * @property-read string $insurerNumber 保険者番号
 * @property-read string $insurerName 保険者名
 * @property-read string $carePlanAuthorOfficeName 居宅介護支援事業所名
 * @property-read string $careManagerName 居宅介護支援事業所：担当者
 * @property-read string $carePlanAuthorOfficeTel 居宅介護支援事業所電話番号
 * @property-read string $createdOn 作成年月日
 * @property-read string $insNumber 被保険者証番号
 * @property-read string $phoneticDisplayName フリガナ：表示用氏名
 * @property-read string $displayName 表示用氏名
 * @property-read \Domain\Common\Carbon $birthday 生年月日
 * @property-read \Domain\Common\Sex $sex 性別
 * @property-read \Domain\LtcsInsCard\LtcsLevel $ltcsLevel 要介護度（要介護状態区分等）
 * @property-read \Domain\LtcsInsCard\LtcsLevel[]&\ScalikePHP\Option $updatedLtcsLevel 変更後要介護度
 * @property-read \Domain\Common\Carbon&\ScalikePHP\Option $ltcsLevelUpdatedOn 要介護度変更日
 * @property-read int $maxBenefit 区分支給限度基準額
 * @property-read string $activatedOn 限度額適用期間（開始）
 * @property-read string $deactivatedOn 限度額適用期間（終了）
 * @property-read array $entries サービス情報
 * @property-read int $currentPageCount 現在ページ数
 * @property-read int $maxPageCount 最大ページ数
 */
final class LtcsProvisionReportSheetPdf extends Model
{
    /** @var int サービス提供票の1枚あたりのサービス情報数 */
    protected const ENTRIES_PER_PAGE = 13;

    /**
     * 介護保険サービス：サービス提供票PDF を生成する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth 月初時点の介護保険被保険者証
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth 月末時点の介護保険被保険者証
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForPlan サービス詳細一覧（予定）
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForResult サービス詳細一覧（実績）
     * @param \Domain\User\User $user 利用者
     * @param \Domain\Common\Carbon $createdOn 作成年月日
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report 予実
     * @param \ScalikePHP\Map&string[] $serviceCodeMap [サービスコード => サービス詳細]
     * @param \Domain\Office\Office $office サービスを行った事業所
     * @param \Domain\Office\Office&\ScalikePHP\Option $carePlanAuthorOfficeOption 居宅介護支援事業所
     * @param bool $needsMaskingInsNumber 被保険者番号マスキング要否
     * @param bool $needsMaskingInsName 被保険者氏名マスキング要否
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetPdf[]&\ScalikePHP\Seq
     */
    public static function from(
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Seq $serviceDetailsForPlan,
        Seq $serviceDetailsForResult,
        User $user,
        Carbon $createdOn,
        LtcsProvisionReport $report,
        Map $serviceCodeMap,
        Office $office,
        Option $carePlanAuthorOfficeOption,
        bool $needsMaskingInsNumber = false,
        bool $needsMaskingInsName = false
    ): Seq {
        $entries = self::entries(
            $report->entries,
            $office,
            $serviceDetailsForPlan,
            $serviceDetailsForResult,
            $serviceCodeMap
        );
        [$phoneticDisplayName, $displayName] = $needsMaskingInsName
            ? [self::maskInsPhoneticName($user->name->phoneticDisplayName), self::maskInsName($user->name->displayName)]
            : [$user->name->phoneticDisplayName, $user->name->displayName];
        $carePlanAuthorOffice = $carePlanAuthorOfficeOption->getOrElseValue([]);
        $hasBeenChanged = LtcsInsCard::levelHasBeenChanged($insCardAtFirstOfMonth, $insCardAtLastOfMonth);
        $maxBenefit = self::resolveMaxBenefit($insCardAtFirstOfMonth, $insCardAtLastOfMonth, $hasBeenChanged);
        [$activatedOn, $deactivatedOn] = self::resolveActivatedRange($insCardAtFirstOfMonth, $insCardAtLastOfMonth, $hasBeenChanged);

        $maxPageCount = Math::ceil($entries->size() / self::ENTRIES_PER_PAGE);
        $xs = array_chunk([...$entries], self::ENTRIES_PER_PAGE);
        return Seq::fromArray($xs)->map(fn (array $entriesPerPage, int $index): self => self::create([
            'status' => $insCardAtLastOfMonth->status,
            'providedIn' => $report->providedIn,
            'insurerNumber' => $insCardAtLastOfMonth->insurerNumber,
            'insurerName' => $insCardAtLastOfMonth->insurerName,
            'carePlanAuthorOfficeName' => $carePlanAuthorOffice->name ?? '',
            'careManagerName' => $insCardAtLastOfMonth->careManagerName,
            'carePlanAuthorOfficeTel' => $carePlanAuthorOffice->tel ?? '',
            'createdOn' => $createdOn->toJapaneseDate(),
            'insNumber' => $needsMaskingInsNumber
                ? self::maskInsNumber($insCardAtLastOfMonth->insNumber)
                : $insCardAtLastOfMonth->insNumber,
            'phoneticDisplayName' => $phoneticDisplayName,
            'displayName' => $displayName,
            'birthday' => $user->birthday,
            'sex' => $user->sex,
            'ltcsLevel' => $hasBeenChanged
                ? LtcsLevel::resolve($insCardAtFirstOfMonth->get()->ltcsLevel) // 要介護度が変わっている場合 $insCardAtFirstOfMonth は必ず some
                : LtcsLevel::resolve($insCardAtLastOfMonth->ltcsLevel),
            'updatedLtcsLevel' => $hasBeenChanged
                ? LtcsLevel::resolve($insCardAtLastOfMonth->ltcsLevel)
                : '',
            'ltcsLevelUpdatedOn' => $hasBeenChanged
                ? $insCardAtLastOfMonth->effectivatedOn->toJapaneseDate()
                : '',
            'maxBenefit' => number_format($maxBenefit),
            'activatedOn' => $activatedOn->toJapaneseYearMonth(),
            'deactivatedOn' => $deactivatedOn->toJapaneseYearMonth(),
            'entries' => $entriesPerPage,
            'currentPageCount' => $index + 1,
            'maxPageCount' => $maxPageCount,
        ]));
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'status',
            'providedIn',
            'insurerNumber',
            'insurerName',
            'carePlanAuthorOfficeName',
            'careManagerName',
            'carePlanAuthorOfficeTel',
            'createdOn',
            'insNumber',
            'phoneticDisplayName',
            'displayName',
            'birthday',
            'sex',
            'ltcsLevel',
            'updatedLtcsLevel',
            'ltcsLevelUpdatedOn',
            'maxBenefit',
            'activatedOn',
            'deactivatedOn',
            'entries',
            'currentPageCount',
            'maxPageCount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'status' => true,
            'providedIn' => true,
            'insurerNumber' => true,
            'insurerName' => true,
            'carePlanAuthorOfficeName' => true,
            'careManagerName' => true,
            'carePlanAuthorOfficeTel' => true,
            'createdOn' => true,
            'insNumber' => true,
            'phoneticDisplayName' => true,
            'displayName' => true,
            'birthday' => true,
            'sex' => true,
            'ltcsLevel' => true,
            'updatedLtcsLevel' => true,
            'ltcsLevelUpdatedOn' => true,
            'maxBenefit' => true,
            'activatedOn' => true,
            'deactivatedOn' => true,
            'entries' => true,
            'currentPageCount' => true,
            'maxPageCount' => true,
        ];
    }

    /**
     * 被保険者番号の先頭6桁を伏せ字にする.
     *
     * @param string $insNumber
     * @return string
     */
    private static function maskInsNumber(string $insNumber): string
    {
        return Strings::mask($insNumber, '*', fn (string $char, int $index): bool => $index < 6);
    }

    /**
     * 被保険者氏名フリガナを伏せ字にする.
     *
     * @param string $insPhoneticName
     * @return string
     */
    private static function maskInsPhoneticName(string $insPhoneticName): string
    {
        return Strings::mask($insPhoneticName, '*', fn (string $char, int $index): bool => $char !== ' ');
    }

    /**
     * 被保険者氏名を伏せ字にする.
     *
     * @param string $insName
     * @return string
     */
    private static function maskInsName(string $insName): string
    {
        $spaceCount = 0;
        return Strings::mask($insName, '●', function (string $char, int $index) use (&$spaceCount): bool {
            if ($char === ' ') {
                ++$spaceCount;
                return false;
            }
            return ($index - $spaceCount) % 2 === 1;
        });
    }

    /**
     * 月間サービス計画及び予定・実績の記録に関するデータを作成する.
     *
     * @param array|\Domain\ProvisionReport\LtcsProvisionReportEntry[] $entries
     * @param Office $office
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForPlan
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForResult
     * @param \ScalikePHP\Map $serviceCodeMap
     * @return \ScalikePHP\Seq
     */
    private static function entries(
        array $entries,
        Office $office,
        Seq $serviceDetailsForPlan,
        Seq $serviceDetailsForResult,
        Map $serviceCodeMap
    ): Seq {
        $plansAdditionMap = $serviceDetailsForPlan
            ->filter(fn (LtcsBillingServiceDetail $x): bool => $x->isAddition)
            ->groupBy(fn (LtcsBillingServiceDetail $x): string => $x->serviceCode->toString());
        $resultsAdditionMap = $serviceDetailsForResult
            ->filter(fn (LtcsBillingServiceDetail $x): bool => $x->isAddition)
            ->groupBy(fn (LtcsBillingServiceDetail $x): string => $x->serviceCode->toString());
        // 日付がある加算の一覧
        $withDateAddition = [
            LtcsServiceCodeCategory::firstTimeAddition(),
            LtcsServiceCodeCategory::emergencyAddition(),
            LtcsServiceCodeCategory::bulkServiceSubtraction1(),
            LtcsServiceCodeCategory::bulkServiceSubtraction2(),
        ];
        // 加算は予実からではなく請求詳細から生成しサービスのあとに追加する
        $additions = Seq::from(...$plansAdditionMap->keys(), ...$resultsAdditionMap->keys())
            ->distinct()
            // サービスコードが数値の場合は int になってしまう PHP のせいで型がかけない
            // int|string になることを想定している
            ->sortBy(fn ($x) => $x)
            ->map(function ($serviceCode) use (
                $serviceCodeMap,
                $withDateAddition,
                $office,
                $resultsAdditionMap,
                $plansAdditionMap
            ) {
                /** @var Seq $plans */
                $plans = $plansAdditionMap->getOrElse($serviceCode, fn (): Seq => Seq::empty());
                /** @var Seq $results */
                $results = $resultsAdditionMap->getOrElse($serviceCode, fn (): Seq => Seq::empty());
                /** @var LtcsBillingServiceDetail $service */
                $service = $plans->isEmpty() ? $results->head() : $plans->head();
                $withDate = in_array($service->serviceCodeCategory, $withDateAddition, true);
                return [
                    // 加算は時間帯が無いので何も表示しない
                    'slot' => '',
                    'serviceName' => $serviceCodeMap->getOrElse($service->serviceCode->toString(), fn (): string => ''),
                    'officeName' => $office->name,
                    'plans' => $withDate ? $plans->toMap(fn (LtcsBillingServiceDetail $x): int => $x->providedOn->day) : Map::empty(),
                    'results' => $withDate ? $results->toMap(fn (LtcsBillingServiceDetail $x): int => $x->providedOn->day) : Map::empty(),
                    'plansCount' => count($plans),
                    'resultsCount' => count($results),
                ];
            });
        return Seq::fromArray($entries)
            ->filter(fn (LtcsProvisionReportEntry $entry): bool => $entry->category !== LtcsProjectServiceCategory::ownExpense())
            ->sortBy(fn (LtcsProvisionReportEntry $entry): string => $entry->serviceCode->toString())
            ->map(fn (LtcsProvisionReportEntry $entry) => [
                'slot' => $entry->slot->start . '〜' . $entry->slot->end,
                'serviceName' => $serviceCodeMap->getOrElse($entry->serviceCode->toString(), fn (): string => ''),
                'officeName' => $office->name,
                'plans' => Seq::fromArray($entry->plans)->toMap(fn (Carbon $x): int => $x->day),
                'results' => Seq::fromArray($entry->results)->toMap(fn (Carbon $x): int => $x->day),
                'plansCount' => count($entry->plans),
                'resultsCount' => count($entry->results),
            ])->append($additions);
    }

    /**
     * 要介護状態区分に対応する区分支給限度基準額を導出する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonthOption
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth
     * @param bool $hasBeenChanged
     * @return int
     */
    private static function resolveMaxBenefit(
        Option $insCardAtFirstOfMonthOption,
        LtcsInsCard $insCardAtLastOfMonth,
        bool $hasBeenChanged
    ): int {
        // 要介護度が変更されていた場合、より高い要介護状態区分に対応する区分支給限度基準額を使う
        if ($hasBeenChanged) {
            /** @var \Domain\LtcsInsCard\LtcsInsCard $insCardAtFirstOfMonth */
            $insCardAtFirstOfMonth = $insCardAtFirstOfMonthOption->get(); // 要介護度が変わっている場合 $insCardAtFirstOfMonth は必ず some
            return $insCardAtLastOfMonth->greaterThanForLevel($insCardAtFirstOfMonth)
                ? $insCardAtLastOfMonth->ltcsLevel->maxBenefit()
                : $insCardAtFirstOfMonth->ltcsLevel->maxBenefit();
        }
        return $insCardAtLastOfMonth->ltcsLevel->maxBenefit();
    }

    /**
     * 要介護状態区分に対応する限度額適用期間を導出する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonthOption
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth
     * @param bool $hasBeenChanged
     * @return array
     */
    private static function resolveActivatedRange(
        Option $insCardAtFirstOfMonthOption,
        LtcsInsCard $insCardAtLastOfMonth,
        bool $hasBeenChanged
    ): array {
        // 要介護度が変更されていた場合、より高い要介護状態区分に対応する限度額適用期間を使う
        if ($hasBeenChanged) {
            /** @var \Domain\LtcsInsCard\LtcsInsCard $insCardAtFirstOfMonth */
            $insCardAtFirstOfMonth = $insCardAtFirstOfMonthOption->get(); // 要介護度が変わっている場合 $insCardAtFirstOfMonth は必ず some
            return $insCardAtLastOfMonth->greaterThanForLevel($insCardAtFirstOfMonth)
                ? [$insCardAtLastOfMonth->activatedOn, $insCardAtLastOfMonth->deactivatedOn]
                : [$insCardAtFirstOfMonth->activatedOn, $insCardAtFirstOfMonth->deactivatedOn];
        }
        return [$insCardAtLastOfMonth->activatedOn, $insCardAtLastOfMonth->deactivatedOn];
    }
}
