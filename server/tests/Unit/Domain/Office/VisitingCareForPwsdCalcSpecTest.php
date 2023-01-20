<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\CarbonRange;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\VisitingCareForPwsdCalcSpec} Test.
 */
class VisitingCareForPwsdCalcSpecTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected VisitingCareForPwsdCalcSpec $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (VisitingCareForPwsdCalcSpecTest $self): void {
            $self->values = [
                'officeId' => $self->examples->offices[0]->id,
                'period' => CarbonRange::create(),
                'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::addition1(),
                'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::addition1(),
                'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::addition1(),
                'baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::addition1(),
            ];
            $self->domain = VisitingCareForPwsdCalcSpec::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->domain->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'officeId' => ['officeId'],
                'period' => ['period'],
                'specifiedOfficeAddition' => ['specifiedOfficeAddition'],
                'treatmentImprovementAddition' => ['treatmentImprovementAddition'],
                'specifiedTreatmentImprovementAddition' => ['specifiedTreatmentImprovementAddition'],
                'baseIncreaseSupportAddition' => ['baseIncreaseSupportAddition'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->domain);
        });
    }
}
