<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase;

/**
 * 区分支給限度基準を超える単位数が種類支給限度基準内単位数以下より低い値であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait OverMaxBenefitQuotaScoreUnderBenefitScoreRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOverMaxBenefitQuotaScoreUnderBenefitScore(string $attribute, mixed $value, array $parameters): bool
    {
        $maxBenefitQuotaExcessScore = $value;
        // 区分支給限度基準を超える単位数が0より小さいまたは、数値でない場合は別のバリデーションで検証する
        if ($maxBenefitQuotaExcessScore < 0 || !is_numeric($maxBenefitQuotaExcessScore)) {
            return true;
        }

        $specifiedOfficeAddition = Arr::get($this->data, 'specifiedOfficeAddition');
        $treatmentImprovementAddition = Arr::get($this->data, 'treatmentImprovementAddition');
        $specifiedTreatmentImprovementAddition = Arr::get($this->data, 'specifiedTreatmentImprovementAddition');
        $baseIncreaseSupportAddition = Arr::get($this->data, 'baseIncreaseSupportAddition');
        $locationAddition = Arr::get($this->data, 'locationAddition');

        $isValidAdditions = HomeVisitLongTermCareSpecifiedOfficeAddition::isValid($specifiedOfficeAddition)
            && LtcsTreatmentImprovementAddition::isValid($treatmentImprovementAddition)
            && LtcsSpecifiedTreatmentImprovementAddition::isValid($specifiedTreatmentImprovementAddition)
            && LtcsOfficeLocationAddition::isValid($locationAddition);
        // 加算が正しくない値のときはチェックしない
        if (!$isValidAdditions) {
            return true;
        }

        $entries = Arr::get($this->data, 'entries', []);
        $officeId = (int)Arr::get($this->data, 'officeId');
        $userId = (int)Arr::get($this->data, 'userId');
        $providedInString = Arr::get($this->data, 'providedIn');
        // 取得できないときはチェックしない
        if (empty($providedInString) || empty($entries)) {
            return true;
        }

        /** @var \UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase $useCase */
        $useCase = app(GetLtcsProvisionReportScoreSummaryUseCase::class);

        $planOrResult = explode('.', $attribute)[0];
        $maxBenefitExcessScore = (int)Arr::get($this->data, $planOrResult . '.maxBenefitExcessScore');

        [$planOrResult => $scoreSummary] = $useCase->handle(
            $this->context,
            $officeId,
            $userId,
            Carbon::parse($providedInString),
            Seq::fromArray($entries)->map(function (array $x): LtcsProvisionReportEntry {
                $value = [
                    'slot' => TimeRange::create([
                        'start' => Carbon::now()->format($x['slot']['start']),
                        'end' => Carbon::now()->format($x['slot']['end']),
                    ]),
                    'timeframe' => Timeframe::from($x['timeframe']),
                    'category' => LtcsProjectServiceCategory::from($x['category']),
                    'amounts' => Seq::fromArray($x['amounts'])->map(function (array $amount): LtcsProjectAmount {
                        return LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]);
                    }),
                    'headcount' => $x['headcount'],
                    // API定義には存在する必要があるが、フロントからは来てないのでとりあえずnullにする
                    'ownExpenseProgramId' => $x['ownExpenseProgramId'] ?? null,
                    'serviceCode' => ServiceCode::fromString($x['serviceCode']),
                    'options' => Seq::fromArray($x['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option)),
                    'note' => $x['note'],
                    'plans' => Seq::fromArray($x['plans'])
                        ->map(fn (string $plan): Carbon => Carbon::parse($plan)),
                    'results' => Seq::fromArray($x['results'])
                        ->map(fn (string $results): Carbon => Carbon::parse($results)),
                ];
                return LtcsProvisionReportEntry::create($value);
            }),
            HomeVisitLongTermCareSpecifiedOfficeAddition::from($specifiedOfficeAddition),
            LtcsTreatmentImprovementAddition::from($treatmentImprovementAddition),
            LtcsSpecifiedTreatmentImprovementAddition::from($specifiedTreatmentImprovementAddition),
            LtcsBaseIncreaseSupportAddition::from($baseIncreaseSupportAddition),
            LtcsOfficeLocationAddition::from($locationAddition),
            // TODO: 仮
            new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            )
        );

        // 種類支給限度基準内単位数 = 「限度額管理対象単位数」 - 「種類支給限度基準を超える単位数」
        $benefitScore = $scoreSummary['managedScore'] - $maxBenefitExcessScore;

        // 種類支給限度基準内単位数が0未満のときはここではエラーにしない
        if ($benefitScore < 0) {
            return true;
        }
        return $maxBenefitQuotaExcessScore <= $benefitScore;
    }
}
