<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingBundle} のテスト.
 */
final class DwsBillingBundleTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingBundle $dwsBillingBundle;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingBundleTest $self): void {
            $self->values = [
                'id' => 1,
                'dwsBillingId' => 1,
                'providedIn' => Carbon::today(),
                'cityCode' => '123456',
                'cityName' => '東京都中野区',
                'details' => [
                    DwsBillingServiceDetail::create([
                        'resultId' => $self->examples->attendances[0]->id,
                        'userId' => $self->examples->users[0]->id,
                        'providedOn' => Carbon::today(),
                        'serviceCode' => ServiceCode::fromString('123456'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'isAddition' => false,
                        'score' => 300,
                    ]),
                ],
            ];
            $self->dwsBillingBundle = DwsBillingBundle::create($self->values);
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
            'dwsBillingId' => ['dwsBillingId'],
            'providedIn' => ['providedIn'],
            'cityCode' => ['cityCode'],
            'cityName' => ['cityName'],
            'details' => ['details'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingBundle->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingBundle);
        });
    }
}
