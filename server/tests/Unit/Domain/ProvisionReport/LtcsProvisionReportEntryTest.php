<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportEntry} のテスト
 */
class LtcsProvisionReportEntryTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProvisionReportEntry $ltcsProvisionReportEntry;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProvisionReportEntryTest $self): void {
            $self->values = [
                'slot' => TimeRange::create([
                    'start' => Carbon::now()->format('H:i'),
                    'end' => Carbon::now()->format('H:i'),
                ]),
                'timeframe' => Timeframe::daytime(),
                'category' => LtcsProjectServiceCategory::ownExpense(),
                'amounts' => [
                    LtcsProjectAmount::create([
                        'category' => LtcsProjectAmountCategory::housework(),
                        'amount' => 60,
                    ]),
                ],
                'headcount' => 5,
                'ownExpenseProgramId' => 1,
                'serviceCode' => ServiceCode::fromString('111213'),
                'options' => [ServiceOption::oneOff()],
                'note' => '',
                'plans' => [Carbon::now()],
                'results' => [Carbon::now()],
            ];
            $self->ltcsProvisionReportEntry = LtcsProvisionReportEntry::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'slot' => ['slot'],
            'timeframe' => ['timeframe'],
            'category' => ['category'],
            'amounts' => ['amounts'],
            'headcount' => ['headcount'],
            'ownExpenseProgramId' => ['ownExpenseProgramId'],
            'serviceCode' => ['serviceCode'],
            'options' => ['options'],
            'note' => ['note'],
            'plans' => ['plans'],
            'results' => ['results'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProvisionReportEntry->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsProvisionReportEntry);
        });
    }
}
