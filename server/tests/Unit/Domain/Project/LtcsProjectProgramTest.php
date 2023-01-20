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
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\LtcsProjectProgram} のテスト
 */
class LtcsProjectProgramTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProjectProgram $ltcsProjectProgram;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectProgramTest $self): void {
            $self->values = [
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
                ],
                'headcount' => 2,
                'ownExpenseProgramId' => 1,
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
                'note' => '備考',
            ];
            $self->ltcsProjectProgram = LtcsProjectProgram::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'programIndex' => ['programIndex'],
            'category' => ['category'],
            'recurrence' => ['recurrence'],
            'dayOfWeeks' => ['dayOfWeeks'],
            'slot' => ['slot'],
            'timeframe' => ['timeframe'],
            'amounts' => ['amounts'],
            'headcount' => ['headcount'],
            'ownExpenseProgramId' => ['ownExpenseProgramId'],
            'serviceCode' => ['serviceCode'],
            'options' => ['options'],
            'contents' => ['contents'],
            'note' => ['note'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProjectProgram->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsProjectProgram);
        });
    }
}
