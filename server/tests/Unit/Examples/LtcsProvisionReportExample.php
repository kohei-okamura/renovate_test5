<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

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
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Faker\Generator;

/**
 * LtcsProvisionReport Example.
 *
 * @property-read LtcsProvisionReport[] $ltcsProvisionReports
 * @mixin \Tests\Unit\Examples\OwnExpenseProgramExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\ContractExample
 */
trait LtcsProvisionReportExample
{
    /**
     * 介護保険サービス：予実の一覧を生成する.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]
     */
    protected function ltcsProvisionReports(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsProvisionReport($faker, [
                'id' => 1,
                'fixedAt' => Carbon::create(2021, 1, 28, 12, 34, 56),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 2,
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 3,
                'userId' => $this->users[1]->id,
                'officeId' => $this->offices[1]->id,
                'providedIn' => Carbon::parse('2020-11'),
                'fixedAt' => Carbon::create(2021, 1, 29, 12, 34, 56),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 4,
                'status' => LtcsProvisionReportStatus::fixed(),
                'providedIn' => Carbon::parse('2020-11'),
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 5,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[2]->id,
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
                'status' => LtcsProvisionReportStatus::fixed(),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 6,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[1]->id,
                'providedIn' => Carbon::parse('2020-11'),
                'fixedAt' => Carbon::create(2021, 1, 29, 12, 34, 56),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 7,
                'userId' => $this->users[4]->id,
                'officeId' => $this->offices[2]->id,
                'providedIn' => Carbon::parse($this->contracts[18]->contractedOn->format('Y-m')),
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 8,
                'entries' => [
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[8]->id,
                    ]),
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[9]->id,
                    ]),
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                ],
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 9,
                'entries' => [
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[8]->id,
                    ]),
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[8]->id,
                    ]),
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[8]->id,
                    ]),
                ],
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 10,
                'entries' => [
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[8]->id,
                        'slot' => TimeRange::create([
                            'start' => Carbon::now()->startOfDay()->format('H:i'),
                            'end' => Carbon::now()->startOfDay()->addMinutes(65)->format('H:i'),
                        ]),
                    ]),
                ],
            ]),
            // 自費サービスのみで請求額が 0 円
            $this->generateLtcsProvisionReport($faker, [
                'id' => 11,
                'entries' => [
                    $this->generateLtcsProvisionReportEntry($faker, [
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[12]->id,
                    ]),
                ],
            ]),
            $this->generateLtcsProvisionReport($faker, [
                'id' => 12,
                'providedIn' => Carbon::parse('2020-11'),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 200,
                    maxBenefitQuotaExcessScore: 200,
                ),
            ]),
        ];
    }

    /**
     * Generate an example of LtcsProvisionReport.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    protected function generateLtcsProvisionReport(Generator $faker, array $overwrites): LtcsProvisionReport
    {
        $attrs = [
            'userId' => $this->users[0]->id,
            'officeId' => $this->offices[0]->id,
            'contractId' => $this->contracts[3]->id,
            'providedIn' => Carbon::parse('2020-10'),
            'entries' => [
                $this->generateLtcsProvisionReportEntry($faker),
                $this->generateLtcsProvisionReportEntry($faker, [
                    'plans' => [
                        Carbon::parse('2020-10-12'),
                        Carbon::parse('2020-10-13'),
                    ],
                    'results' => [
                        Carbon::parse('2020-10-12'),
                        Carbon::parse('2020-10-13'),
                    ],
                ]),
            ],
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $faker->numberBetween(10, 9999),
                maxBenefitQuotaExcessScore: $faker->numberBetween(10, 9999),
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $faker->numberBetween(10, 9999),
                maxBenefitQuotaExcessScore: $faker->numberBetween(10, 9999),
            ),
            'status' => LtcsProvisionReportStatus::inProgress(),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return LtcsProvisionReport::create($overwrites + $attrs);
    }

    /**
     * 介護保険サービス：予実：サービス情報 を返す.
     *
     * @param \Faker\Generator $faker
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReportEntry
     */
    private function generateLtcsProvisionReportEntry($faker, array $values = []): LtcsProvisionReportEntry
    {
        return LtcsProvisionReportEntry::create(array_merge([
            'ownExpenseProgramId' => null,
            'slot' => TimeRange::create([
                'start' => Carbon::now()->startOfDay()->format('H:i'),
                'end' => Carbon::now()->startOfDay()->addHours(8)->format('H:i'),
            ]),
            'timeframe' => Timeframe::daytime(),
            'category' => LtcsProjectServiceCategory::physicalCare(),
            'amounts' => [
                LtcsProjectAmount::create([
                    'category' => LtcsProjectAmountCategory::ownExpense(),
                    'amount' => 100,
                ]),
                LtcsProjectAmount::create([
                    'category' => LtcsProjectAmountCategory::housework(),
                    'amount' => 30,
                ]),
            ],
            'headcount' => 5,
            'serviceCode' => ServiceCode::fromString('111213'),
            'options' => [
                ServiceOption::firstTime(),
            ],
            'note' => $faker->realText(100),
            'plans' => [
                Carbon::parse('2020-10-10'),
                Carbon::parse('2020-10-11'),
            ],
            'results' => [
                Carbon::parse('2020-10-10'),
                Carbon::parse('2020-10-11'),
            ],
        ], $values));
    }
}
