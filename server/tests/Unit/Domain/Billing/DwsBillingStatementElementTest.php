<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementElement;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementElement} のテスト.
 */
final class DwsBillingStatementElementTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    protected DwsBillingStatementElement $dwsBillingStatementElement;

    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'serviceCode' => ServiceCode::fromString('111111'),
                'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                'unitScore' => 100,
                'count' => 1,
                'isAddition' => false,
                'providedOn' => Carbon::now(),
            ];
            $self->dwsBillingStatementElement = DwsBillingStatementElement::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'with serviceCode' => ['serviceCode'],
            'with serviceCodeCategory' => ['serviceCodeCategory'],
            'with unitScore' => ['unitScore'],
            'with count' => ['count'],
            'with isAddition' => ['isAddition'],
            'with providedOn' => ['providedOn'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingStatementElement->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingStatementElement);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toItem(): void
    {
        $this->should('get DwsBillingStatementItem', function (): void {
            $this->assertMatchesModelSnapshot($this->dwsBillingStatementElement->toItem());
        });
    }
}
