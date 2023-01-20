<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\DwsProject} Test.
 */
class DwsProjectTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Project\DwsProject
     */
    private function createInstance(array $attrs = []): DwsProject
    {
        $values = [
            'id' => 1,
            'organizationId' => 2,
            'contractId' => 3,
            'officeId' => 4,
            'userId' => 5,
            'staffId' => 6,
            'writtenOn' => Carbon::today()->subDay(),
            'effectivatedOn' => Carbon::tomorrow(),
            'requestFromUser' => 'できるだけ自分でできることは自分でやりたい。',
            'requestFromFamily' => '自由にさせてやりたい。',
            'objective' => '在宅中の転倒や誤飲を防止し、負担を軽減できるようサポートする。',
            'programs' => [
                DwsProjectProgram::create([
                    'summaryIndex' => 1,
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'recurrence' => Recurrence::everyWeek(),
                    'dayOfWeeks' => [DayOfWeek::mon(), DayOfWeek::wed(), DayOfWeek::fri()],
                    'slot' => TimeRange::create(['start' => '10:00', 'end' => '18:00']),
                    'headcount' => 1,
                    'ownExpenseProgramId' => null,
                    'options' => [
                        ServiceOption::sucking(),
                        ServiceOption::over20(),
                    ],
                    'contents' => [
                        DwsProjectContent::create([
                            'menuId' => 123,
                            'duration' => 60,
                            'content' => '夜ごはんのお買い物へ一緒に行き、料理を行う。',
                            'memo' => '火、包丁を使わない作業をしていただく。',
                        ]),
                    ],
                    'note' => '備考',
                ]),
            ],
            'isEnabled' => true,
            'version' => 12,
            'createdAt' => Carbon::now()->subDay(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsProject::create($attrs + $values);
    }
}
