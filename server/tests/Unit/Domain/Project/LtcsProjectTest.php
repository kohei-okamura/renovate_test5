<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

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
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\LtcsProject} Test.
 */
class LtcsProjectTest extends Test
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
     * @return \Domain\Project\LtcsProject
     */
    private function createInstance(array $attrs = []): LtcsProject
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
            'problem' => '認知症があるが、病状を悪化させない。安全に家事ができるようになる。',
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::create(2019, 1, 28),
                    'end' => Carbon::create(2020, 3, 31),
                ]),
                'text' => '自分で薬管理ができる。できる家事を継続する。',
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::create(2019, 1, 28),
                    'end' => Carbon::create(2019, 3, 31),
                ]),
                'text' => '服薬忘れを防ぐ為、声掛けにて服薬を促す。できる家事は継続して行っていただく。',
            ]),
            'programs' => [
                LtcsProjectProgram::create([
                    'programIndex' => 1,
                    'category' => LtcsProjectServiceCategory::physicalCare(),
                    'recurrence' => Recurrence::everyWeek(),
                    'dayOfWeeks' => [DayOfWeek::mon(), DayOfWeek::wed(), DayOfWeek::fri()],
                    'slot' => TimeRange::create(['start' => '15:00', 'end' => '17:00']),
                    'timeframe' => Timeframe::daytime(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 120,
                        ]),
                    ],
                    'headcount' => 1,
                    'ownExpenseProgramId' => null,
                    'serviceCode' => ServiceCode::fromString('112345'),
                    'options' => [],
                    'contents' => [
                        LtcsProjectContent::create([
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
        return LtcsProject::create($attrs + $values);
    }
}
