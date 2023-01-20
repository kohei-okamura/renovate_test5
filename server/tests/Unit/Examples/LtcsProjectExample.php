<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\Objective;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Faker\Generator;

/**
 * LtcsProject Example.
 *
 * @mixin \Tests\Unit\Examples\ContractExample
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\StaffExample
 * @property-read \Domain\Project\LtcsProject[] $ltcsProjects
 */
trait LtcsProjectExample
{
    /**
     * 介護保険サービス計画の一覧を生成する.
     *
     * @return \Domain\Project\LtcsProject[]
     */
    protected function ltcsProjects(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsProject([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
            ], $faker),
            $this->generateLtcsProject([
                'id' => 7,
                'organizationId' => $this->organizations[1]->id,
                'officeId' => $this->offices[1]->id,
            ], $faker),
            $this->generateLtcsProject([
                'id' => 8,
                'organizationId' => $this->organizations[4]->id,
                'officeId' => $this->offices[2]->id,
            ], $faker),
            $this->generateLtcsProject([
                'id' => 9,
                'organizationId' => $this->organizations[5]->id,
                'officeId' => $this->offices[3]->id,
            ], $faker),
            $this->generateLtcsProject([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'programs' => [
                    LtcsProjectProgram::create([
                        'programIndex' => 1,
                        'category' => LtcsProjectServiceCategory::housework(),
                        'recurrence' => Recurrence::evenWeek(),
                        'dayOfWeeks' => [
                            DayOfWeek::mon(),
                            DayOfWeek::wed(),
                        ],
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'timeframe' => Timeframe::daytime(),
                        'amounts' => [
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::housework(),
                                'amount' => 60,
                            ]),
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::physicalCare(),
                                'amount' => 60,
                            ]),
                        ],
                        'headcount' => 2,
                        'ownExpenseProgramId' => null,
                        'serviceCode' => ServiceCode::fromString('111312'),
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'contents' => [
                            LtcsProjectContent::create([
                                'menuId' => 1,
                                'duration' => 60,
                                'content' => '掃除',
                                'memo' => '特になし',
                            ]),
                            LtcsProjectContent::create([
                                'menuId' => 2,
                                'duration' => 60,
                                'content' => '洗濯',
                                'memo' => '特になし',
                            ]),
                        ],
                        'note' => str_repeat('あ', 32),
                    ]),
                    LtcsProjectProgram::create([
                        'programIndex' => 2,
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'recurrence' => Recurrence::evenWeek(),
                        'dayOfWeeks' => [
                            DayOfWeek::tue(),
                            DayOfWeek::thu(),
                        ],
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'timeframe' => Timeframe::daytime(),
                        'amounts' => [
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::housework(),
                                'amount' => 60,
                            ]),
                        ],
                        'headcount' => 2,
                        'ownExpenseProgramId' => 3,
                        'serviceCode' => ServiceCode::fromString('111312'),
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'contents' => [
                            LtcsProjectContent::create([
                                'menuId' => 1,
                                'duration' => 60,
                                'content' => '掃除',
                                'memo' => '特になし',
                            ]),
                        ],
                        'note' => str_repeat('あ', 32),
                    ]),
                ],
            ], $faker),
        ];
    }

    /**
     * Generate an example of LtcsProject.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Project\LtcsProject
     */
    protected function generateLtcsProject(array $overwrites, Generator $faker): LtcsProject
    {
        $attrs = [
            'organizationId' => $this->organizations[0]->id,
            'contractId' => $this->contracts[3]->id,
            'officeId' => $this->offices[0]->id,
            'userId' => $this->users[0]->id,
            'staffId' => $this->staffs[0]->id,
            'writtenOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'effectivatedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            'requestFromUser' => $faker->text(255),
            'requestFromFamily' => $faker->text(255),
            'problem' => $faker->text(255),
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->addMonths(6),
                ]),
                'text' => $faker->text(255),
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->addWeek(),
                ]),
                'text' => $faker->text(255),
            ]),
            'programs' => [
                LtcsProjectProgram::create([
                    'programIndex' => 1,
                    'category' => LtcsProjectServiceCategory::housework(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::mon(),
                        DayOfWeek::wed(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'timeframe' => Timeframe::daytime(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::housework(),
                            'amount' => 60,
                        ]),
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 60,
                        ]),
                    ],
                    'headcount' => 2,
                    'ownExpenseProgramId' => null,
                    'serviceCode' => ServiceCode::fromString('111312'),
                    'options' => [
                        ServiceOption::vitalFunctionsImprovement1(),
                    ],
                    'contents' => [
                        LtcsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                        LtcsProjectContent::create([
                            'menuId' => 2,
                            'duration' => 60,
                            'content' => '洗濯',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => $faker->realText(100),
                ]),
                LtcsProjectProgram::create([
                    'programIndex' => 2,
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::tue(),
                        DayOfWeek::thu(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'timeframe' => Timeframe::daytime(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::housework(),
                            'amount' => 60,
                        ]),
                    ],
                    'headcount' => 2,
                    'ownExpenseProgramId' => 2,
                    'serviceCode' => ServiceCode::fromString('111312'),
                    'options' => [
                    ],
                    'contents' => [
                        LtcsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => $faker->realText(100),
                ]),
            ],
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return LtcsProject::create($overwrites + $attrs);
    }
}
