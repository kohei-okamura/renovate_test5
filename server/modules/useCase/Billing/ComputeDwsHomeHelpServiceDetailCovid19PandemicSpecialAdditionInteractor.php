<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as DictionaryEntry;
use Lib\Math;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：令和3年9月30日までの上乗せ分のサービス詳細組み立て実装（居宅介護用）.
 */
final class ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionInteractor implements ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase
{
    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsProvisionReport $provisionReport,
        int $baseScore,
        Option $dictionaryEntryOption
    ): Seq {
        return $provisionReport->providedIn->between('2021-04-01', '2021-09-30')
            ? Seq::from(...$this->forAprilToSeptember2021($provisionReport, $baseScore, $dictionaryEntryOption))
            : Seq::empty();
    }

    /**
     * 令和3年9月30日までの上乗せ分を生成する.
     *
     * - 加算の単位数 = 基本報酬の合計単位数 * 0.1% （四捨五入）
     * - 計算後0単位となる場合（合計単位数が500単位未満）は1単位の上乗せを行う
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|\ScalikePHP\Option $dictionaryEntryOption
     * @return \Domain\Billing\DwsBillingServiceDetail[]|iterable
     */
    private function forAprilToSeptember2021(
        DwsProvisionReport $provisionReport,
        int $baseScore,
        Option $dictionaryEntryOption
    ): iterable {
        return $dictionaryEntryOption
            ->toSeq()
            ->map(function (DictionaryEntry $entry) use ($provisionReport, $baseScore): DwsBillingServiceDetail {
                $score = $baseScore < 500 ? 1 : Math::round($baseScore * 0.001);
                return DwsBillingServiceDetail::create([
                    'userId' => $provisionReport->userId,
                    'providedOn' => $provisionReport->providedIn->endOfMonth(),
                    'serviceCode' => $entry->serviceCode,
                    'serviceCodeCategory' => $entry->category,
                    'unitScore' => $score,
                    'isAddition' => true,
                    'count' => 1,
                    'totalScore' => $score,
                ]);
            });
    }
}
