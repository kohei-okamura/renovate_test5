<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\Shift\ServiceOption;
use Faker\Generator;

/**
 * DwsProvisionReport Example.
 *
 * @property-read \Domain\ProvisionReport\DwsProvisionReport[] $dwsProvisionReports
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\ContractExample
 * @mixin \Tests\Unit\Examples\OwnExpenseProgramExample
 */
trait DwsProvisionReportExample
{
    /**
     * 障害福祉サービス：予実の一覧を生成する.
     *
     * @return \Domain\ProvisionReport\DwsProvisionReport[]
     */
    protected function dwsProvisionReports(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsProvisionReport($faker, [
                'id' => 1,
                'userId' => $this->users[0]->id,
                'providedIn' => Carbon::parse('2020-10'),
                'fixedAt' => Carbon::create(2021, 1, 28, 12, 34, 56),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 2,
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker),
                ],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker),
                ],
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 3,
                'officeId' => $this->offices[1]->id,
                'fixedAt' => Carbon::create(2021, 1, 29, 12, 34, 56),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 4,
                'userId' => $this->users[0]->id,
                'status' => DwsProvisionReportStatus::fixed(),
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 5,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[2]->id,
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
                'status' => DwsProvisionReportStatus::fixed(),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 6,
                'userId' => $this->users[0]->id,
                'officeId' => $this->offices[1]->id,
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 7,
                'userId' => $this->users[3]->id,
                'officeId' => $this->offices[2]->id,
                'providedIn' => Carbon::parse($this->contracts[17]->contractedOn->format('Y-m')),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 8,
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'ownExpenseProgramId' => $this->ownExpensePrograms[0]->id,
                    ]),
                ],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'ownExpenseProgramId' => $this->ownExpensePrograms[1]->id,
                    ]),
                ],
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 9,
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker),
                ],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[0]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[6]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[7]->id,
                    ]),
                ],
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 10,
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[0]->id,
                    ]),
                ],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[0]->id,
                    ]),
                ],
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 11,
                'results' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                ],
                'fixedAt' => Carbon::create(2021, 1, 28, 15, 0, 0),
            ]),
            $this->generateDwsProvisionReport($faker, [
                'id' => 12,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[2]->id,
                'providedIn' => Carbon::parse('2022-10'),
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
                'status' => DwsProvisionReportStatus::fixed(),
                'plans' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
            ]),
            // 契約に居宅の初回サービス提供日だけ設定された利用者の予実（居宅のみ）
            $this->generateDwsProvisionReport($faker, [
                'id' => 13,
                'userId' => $this->users[2]->id,
                'officeId' => $this->offices[2]->id,
                'providedIn' => Carbon::parse('2022-11'),
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
                'status' => DwsProvisionReportStatus::fixed(),
                'plans' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::accompany(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::accompany(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
            ]),
            // 契約に重訪の初回サービス提供日だけ設定された利用者の予実（重訪のみ）
            $this->generateDwsProvisionReport($faker, [
                'id' => 14,
                'userId' => $this->users[3]->id,
                'officeId' => $this->offices[2]->id,
                'providedIn' => Carbon::parse('2022-11'),
                'fixedAt' => Carbon::create(2021, 1, 20, 15, 0, 0),
                'status' => DwsProvisionReportStatus::fixed(),
                'plans' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2040, 11, 12),
                            'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                            'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'headcount' => 1,
                        'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                        'movingDurationMinutes' => 60,
                        'note' => $faker->realText(100),
                    ]),
                ],
            ]),
            // 予定あり、実績なし
            $this->generateDwsProvisionReport($faker, [
                'id' => 15,
                'userId' => $this->users[1]->id,
                'status' => DwsProvisionReportStatus::fixed(),
                'providedIn' => Carbon::parse('2021-06'),
                'fixedAt' => Carbon::create(2021, 7, 14),
                'results' => [],
            ]),
            // 予定なし、実績あり
            $this->generateDwsProvisionReport($faker, [
                'id' => 16,
                'userId' => $this->users[1]->id,
                'status' => DwsProvisionReportStatus::fixed(),
                'providedIn' => Carbon::parse('2021-07'),
                'fixedAt' => Carbon::create(2021, 8, 16),
                'plans' => [],
            ]),
            // 自費サービスのみ
            $this->generateDwsProvisionReport($faker, [
                'id' => 17,
                'userId' => $this->users[1]->id,
                'status' => DwsProvisionReportStatus::fixed(),
                'providedIn' => Carbon::parse('2021-08'),
                'fixedAt' => Carbon::create(2021, 9, 18),
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                ],
                'results' => [],
            ]),
            // 自費サービス サービス時間が端数の予実
            $this->generateDwsProvisionReport($faker, [
                'id' => 18,
                'userId' => $this->users[1]->id,
                'status' => DwsProvisionReportStatus::fixed(),
                'providedIn' => Carbon::parse('2021-09'),
                'fixedAt' => Carbon::create(2021, 9, 18),
                'plans' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                ],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2021, 2, 9),
                            'start' => Carbon::create(2021, 2, 9, 0, 15, 0),
                            'end' => Carbon::create(2021, 2, 9, 1, 20, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'headcount' => 2,
                        'ownExpenseProgramId' => $this->ownExpensePrograms[10]->id,
                    ]),
                ],

            ]),
            // 自費サービスのみで請求額が 0 円
            $this->generateDwsProvisionReport($faker, [
                'id' => 19,
                'providedIn' => Carbon::parse('2021-09'),
                'fixedAt' => Carbon::create(2021, 9, 18),
                'plans' => [/* 請求なので予定は不要 */],
                'results' => [
                    $this->generateDwsProvisionReportItem($faker, [
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'ownExpenseProgramId' => $this->ownExpensePrograms[11]->id,
                    ]),
                ],
            ]),
        ];
    }

    /**
     * Generate an example of DwsProvisionReport.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    protected function generateDwsProvisionReport(Generator $faker, array $overwrites): DwsProvisionReport
    {
        $attrs = [
            'userId' => $this->users[1]->id,
            'officeId' => $this->offices[0]->id,
            'contractId' => $this->contracts[3]->id,
            'providedIn' => Carbon::parse('2020-09'),
            'plans' => [
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2040, 11, 12),
                        'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                        'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                    'movingDurationMinutes' => 60,
                    'note' => $faker->realText(100),
                ]),
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2040, 11, 12),
                        'start' => Carbon::create(2040, 11, 12, 13, 10, 0),
                        'end' => Carbon::create(2040, 11, 12, 14, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                    'movingDurationMinutes' => 60,
                    'note' => $faker->realText(100),
                ]),
            ],
            'results' => [
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2040, 11, 12),
                        'start' => Carbon::create(2040, 11, 12, 11, 10, 0),
                        'end' => Carbon::create(2040, 11, 12, 12, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                    'movingDurationMinutes' => 60,
                    'note' => $faker->realText(100),
                ]),
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2040, 11, 12),
                        'start' => Carbon::create(2040, 11, 12, 13, 10, 0),
                        'end' => Carbon::create(2040, 11, 12, 14, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [ServiceOption::firstTime(), ServiceOption::sucking()],
                    'movingDurationMinutes' => 60,
                    'note' => $faker->realText(100),
                ]),
            ],
            'status' => DwsProvisionReportStatus::inProgress(),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsProvisionReport::create($overwrites + $attrs);
    }

    /**
     * 障害福祉サービス：予実：要素 を返す.
     *
     * @param \Faker\Generator $faker
     * @param array $values
     * @return \Domain\ProvisionReport\DwsProvisionReportItem
     */
    private function generateDwsProvisionReportItem($faker, array $values = []): DwsProvisionReportItem
    {
        return DwsProvisionReportItem::create(array_merge([
            'schedule' => Schedule::create([
                'date' => Carbon::create(2021, 2, 9),
                'start' => Carbon::create(2021, 2, 9, 23, 15, 0),
                'end' => Carbon::create(2021, 2, 10, 7, 15, 0),
            ]),
            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'ownExpenseProgramId' => null,
            'options' => $faker->randomElements(ServiceOption::all(), 1, false),
            'note' => $faker->realText(100),
        ], $values));
    }
}
