<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Common\Carbon;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\DwsProvisionReport} のテスト
 */
class DwsProvisionReportTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsProvisionReport $dwsProvisionReport;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProvisionReportTest $self): void {
            $self->values = [
                'id' => 1,
                'userId' => 1,
                'officeId' => 1,
                'contractId' => 1,
                'providedIn' => Carbon::now(),
                'plans' => [DwsProvisionReportItem::create()],
                'results' => [DwsProvisionReportItem::create()],
                'status' => DwsProvisionReportStatus::fixed(),
                'fixedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->dwsProvisionReport = DwsProvisionReport::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'userId' => ['userId'],
            'officeId' => ['officeId'],
            'contractId' => ['contractId'],
            'providedIn' => ['providedIn'],
            'plans' => ['plans'],
            'results' => ['results'],
            'status' => ['status'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsProvisionReport->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsProvisionReport);
        });
    }
}
