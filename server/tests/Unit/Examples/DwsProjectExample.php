<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Faker\Generator;

/**
 * DwsProject Example.
 *
 * @mixin \Tests\Unit\Examples\ContractExample
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\StaffExample
 * @property-read \Domain\Project\DwsProject[] $dwsProjects
 */
trait DwsProjectExample
{
    /**
     * 障害福祉サービス計画の一覧を生成する.
     *
     * @return \Domain\Project\DwsProject[]
     */
    protected function dwsProjects(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsProject([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
            ], $faker),
            $this->generateDwsProject([
                'id' => 2,
                'organizationId' => $this->organizations[1]->id,
                'officeId' => $this->offices[1]->id,
            ], $faker),
            $this->generateDwsProject([
                'id' => 3,
                'organizationId' => $this->organizations[4]->id,
                'officeId' => $this->offices[2]->id,
            ], $faker),
            $this->generateDwsProject([
                'id' => 4,
                'organizationId' => $this->organizations[5]->id,
                'officeId' => $this->offices[3]->id,
            ], $faker),
            $this->generateDwsProject([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'programs' => [
                    DwsProjectProgram::create([
                        'summaryIndex' => 1,
                        'category' => DwsProjectServiceCategory::housework(),
                        'recurrence' => Recurrence::evenWeek(),
                        'dayOfWeeks' => [
                            DayOfWeek::mon(),
                            DayOfWeek::wed(),
                        ],
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'headcount' => 2,
                        'ownExpenseProgramId' => null,
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'contents' => [
                            DwsProjectContent::create([
                                'menuId' => 1,
                                'duration' => 60,
                                'content' => '掃除',
                                'memo' => '特になし',
                            ]),
                            DwsProjectContent::create([
                                'menuId' => 2,
                                'duration' => 60,
                                'content' => '洗濯',
                                'memo' => '特になし',
                            ]),
                        ],
                        'note' => $faker->realText(100),
                    ]),
                    DwsProjectProgram::create([
                        'summaryIndex' => 2,
                        'category' => DwsProjectServiceCategory::ownExpense(),
                        'recurrence' => Recurrence::evenWeek(),
                        'dayOfWeeks' => [
                            DayOfWeek::tue(),
                            DayOfWeek::thu(),
                        ],
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'headcount' => 2,
                        'ownExpenseProgramId' => 3,
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'contents' => [
                            DwsProjectContent::create([
                                'menuId' => 1,
                                'duration' => 60,
                                'content' => '掃除',
                                'memo' => '特になし',
                            ]),
                        ],
                        'note' => $faker->realText(100),
                    ]),
                ],
            ], $faker),
        ];
    }

    /**
     * Generate an example of DwsProject.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Project\DwsProject
     */
    protected function generateDwsProject(array $overwrites, Generator $faker): DwsProject
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
            'objective' => $faker->text(255),
            'programs' => [
                DwsProjectProgram::create([
                    'summaryIndex' => 1,
                    'category' => DwsProjectServiceCategory::housework(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::mon(),
                        DayOfWeek::wed(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'headcount' => 2,
                    'ownExpenseProgramId' => null,
                    'options' => [
                        ServiceOption::sucking(),
                    ],
                    'contents' => [
                        DwsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                        DwsProjectContent::create([
                            'menuId' => 2,
                            'duration' => 60,
                            'content' => '洗濯',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => $faker->realText(100),
                ]),
                DwsProjectProgram::create([
                    'summaryIndex' => 2,
                    'category' => DwsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::tue(),
                        DayOfWeek::thu(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'headcount' => 2,
                    'ownExpenseProgramId' => 2,
                    'options' => [],
                    'contents' => [
                        DwsProjectContent::create([
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
        return DwsProject::create($overwrites + $attrs);
    }
}
