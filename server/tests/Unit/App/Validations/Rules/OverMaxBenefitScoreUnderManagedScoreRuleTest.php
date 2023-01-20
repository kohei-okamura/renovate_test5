<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\ProvisionReport\LtcsProvisionReport;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\GetLtcsProvisionReportScoreSummaryUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\OverMaxBenefitScoreUnderManagedScoreRule} のテスト.
 */
final class OverMaxBenefitScoreUnderManagedScoreRuleTest extends Test
{
    use ExamplesConsumer;
    use GetLtcsProvisionReportScoreSummaryUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private LtcsProvisionReport $ltcsProvisionReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->getLtcsProvisionReportScoreSummaryUseCase
                ->allows('handle')
                ->andReturn([
                    'plan' => ['managedScore' => 10000, 'unmanagedScore' => 10000],
                    'result' => ['managedScore' => 10000, 'unmanagedScore' => 10000],
                ])
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateCopayUnderCoapyLimit(): void
    {
        $this->should(
            'pass when plan maxBenefitExcessScore is under managedScore',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        ...$this->createParams(),
                    ],
                    ['plan.maxBenefitExcessScore' => 'over_max_benefit_score_under_managed_score']
                );
                $this->assertTrue($validator->passes());
            }
        );
        $this->should(
            'pass when result maxBenefitExcessScore is under managedScore',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        ...$this->createParams(),
                    ],
                    ['result.maxBenefitExcessScore' => 'over_max_benefit_score_under_managed_score']
                );
                $this->assertTrue($validator->passes());
            }
        );
        $this->should(
            'fail when plan OverMaxBenefitScore is not under managedScore',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        ...$this->createParams([
                            'plan' => [
                                'maxBenefitExcessScore' => 10001,
                                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
                            ],
                        ]),
                    ],
                    ['plan.maxBenefitExcessScore' => 'over_max_benefit_score_under_managed_score']
                );
                $this->assertFalse($validator->passes());
            }
        );
        $this->should(
            'fail when result OverMaxBenefitScore is not under managedScore',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        ...$this->createParams([
                            'result' => [
                                'maxBenefitExcessScore' => 10001,
                                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
                            ],
                        ]),
                    ],
                    ['result.maxBenefitExcessScore' => 'over_max_benefit_score_under_managed_score']
                );
                $this->assertFalse($validator->passes());
            }
        );
    }

    private function createParams(array $overwrites = []): array
    {
        return [
            'userId' => $this->ltcsProvisionReport->userId,
            'officeId' => $this->ltcsProvisionReport->officeId,
            'contractId' => $this->ltcsProvisionReport->contractId,
            'providedIn' => $this->ltcsProvisionReport->providedIn,
            'entries' => $this->ltcsProvisionReport->entries,
            'specifiedOfficeAddition' => $this->ltcsProvisionReport->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $this->ltcsProvisionReport->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $this->ltcsProvisionReport->specifiedTreatmentImprovementAddition->value(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()->value(),
            'locationAddition' => $this->ltcsProvisionReport->locationAddition->value(),
            'plan' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
            ],
            'result' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->result->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
            ],
            ...$overwrites,
        ];
    }
}
