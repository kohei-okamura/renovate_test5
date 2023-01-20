<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\DwsProjectProgram} のテスト
 */
class DwsProjectProgramTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsProjectProgram $dwsProjectProgram;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProjectProgramTest $self): void {
            $self->values = [
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
                'ownExpenseProgramId' => 1,
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
                'note' => '備考',
            ];
            $self->dwsProjectProgram = DwsProjectProgram::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'summaryIndex' => ['summaryIndex'],
            'category' => ['category'],
            'recurrence' => ['recurrence'],
            'dayOfWeeks' => ['dayOfWeeks'],
            'slot' => ['slot'],
            'headcount' => ['headcount'],
            'ownExpenseProgramId' => ['ownExpenseProgramId'],
            'options' => ['options'],
            'contents' => ['contents'],
            'note' => ['note'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsProjectProgram->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsProjectProgram);
        });
    }
}
