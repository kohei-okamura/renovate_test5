<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceDetail} のテスト.
 */
class DwsBillingServiceDetailTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingServiceDetail $dwsBillingServiceDetail;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingServiceDetailTest $self): void {
            $self->values = [
                'userId' => $self->examples->users[0]->id,
                'providedOn' => Carbon::today(),
                'serviceCode' => ServiceCode::fromString('123456'),
                'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                'isAddition' => false,
                'unitScore' => 300,
                'count' => 2,
                'totalScore' => 600,
            ];
            $self->dwsBillingServiceDetail = DwsBillingServiceDetail::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'userId' => ['userId'],
            'providedOn' => ['providedOn'],
            'serviceCode' => ['serviceCode'],
            'serviceCodeCategory' => ['serviceCodeCategory'],
            'unitScore' => ['unitScore'],
            'isAddition' => ['isAddition'],
            'count' => ['count'],
            'totalScore' => ['totalScore'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingServiceDetail->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingServiceDetail);
        });
    }
}
