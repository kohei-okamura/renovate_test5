<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReport} のテスト
 */
class LtcsProvisionReportTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProvisionReport $ltcsProvisionReport;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProvisionReportTest $self): void {
            $self->values = [
                'id' => 1,
                'userId' => 1,
                'officeId' => 1,
                'contractId' => 1,
                'providedIn' => Carbon::now(),
                'entries' => [LtcsProvisionReportEntry::create()],
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
                'locationAddition' => LtcsOfficeLocationAddition::specifiedArea(),
                'plan' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'status' => LtcsProvisionReportStatus::fixed(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->ltcsProvisionReport = LtcsProvisionReport::create($self->values);
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
            'entries' => ['entries'],
            'specifiedOfficeAddition' => ['specifiedOfficeAddition'],
            'treatmentImprovementAddition' => ['treatmentImprovementAddition'],
            'specifiedTreatmentImprovementAddition' => ['specifiedTreatmentImprovementAddition'],
            'baseIncreaseSupportAddition' => ['baseIncreaseSupportAddition'],
            'locationAddition' => ['locationAddition'],
            'plan' => ['plan'],
            'result' => ['result'],
            'status' => ['status'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProvisionReport->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsProvisionReport);
        });
    }
}
