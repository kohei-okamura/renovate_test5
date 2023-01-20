<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry as DictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Lib\Exceptions\SetupException;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：令和3年9月30日までの上乗せ分のサービス詳細組み立て実装.
 */
class ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor implements ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase
{
    /** {@inheritdoc} */
    public function handle(LtcsProvisionReport $provisionReport, Seq $dictionaryEntries, int $mainScore): Seq
    {
        return $mainScore !== 0 && $provisionReport->providedIn->between('2021-04-01', '2021-09-30')
            ? Seq::from($this->forAprilToSeptember2021($dictionaryEntries, $provisionReport, $mainScore))
            : Seq::empty();
    }

    /**
     * 令和3年9月30日までの上乗せ分を生成する.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport
     * @param int $mainScore
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function forAprilToSeptember2021(
        Seq $dictionaryEntries,
        LtcsProvisionReport $provisionReport,
        int $mainScore
    ): LtcsBillingServiceDetail {
        $entry = $this->getCovid19PandemicSpecialAdditionEntry($dictionaryEntries);
        $score = $mainScore < 500 ? 1 : Math::round($mainScore * 0.001);
        return new LtcsBillingServiceDetail(
            userId: $provisionReport->userId,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: $provisionReport->providedIn->endOfMonth(),
            serviceCode: $entry->serviceCode,
            serviceCodeCategory: $entry->category,
            buildingSubtraction: LtcsBuildingSubtraction::none(), // TODO: DEV-5851 同一建物減算正式対応時に修正する
            noteRequirement: $entry->noteRequirement,
            isAddition: true,
            isLimited: $entry->isLimited,
            durationMinutes: 0,
            unitScore: $score,
            count: 1,
            wholeScore: $score,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: $score,
        );
    }

    /**
     * 令和3年9月30日までの上乗せ分のサービスコード辞書エントリを取得する.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry
     */
    private function getCovid19PandemicSpecialAdditionEntry(Seq $dictionaryEntries): DictionaryEntry
    {
        return $dictionaryEntries
            ->find(function (DictionaryEntry $x): bool {
                return $x->category === LtcsServiceCodeCategory::covid19PandemicSpecialAddition();
            })
            ->getOrElse(function (): void {
                throw new SetupException('Covid19PandemicSpecialAdditionEntry not found.');
            });
    }
}
