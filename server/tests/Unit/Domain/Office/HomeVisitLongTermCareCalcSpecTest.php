<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\CarbonRange;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\HomeVisitLongTermCareCalcSpec} Test.
 */
class HomeVisitLongTermCareCalcSpecTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected HomeVisitLongTermCareCalcSpec $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HomeVisitLongTermCareCalcSpecTest $self): void {
            $self->values = [
                'officeId' => $self->examples->offices[0]->id,
                'period' => CarbonRange::create(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
                'locationAddition' => LtcsOfficeLocationAddition::mountainousArea(),
                'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition2(),
                'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
            ];
            $self->domain = HomeVisitLongTermCareCalcSpec::create($self->values);
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
                'locationAddition' => ['locationAddition'],
                'specifiedTreatmentImprovementAddition' => ['specifiedTreatmentImprovementAddition'],
                'treatmentImprovementAddition' => ['treatmentImprovementAddition'],
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
