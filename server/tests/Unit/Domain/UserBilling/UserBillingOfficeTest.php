<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\UserBilling\UserBillingOffice;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingOffice} のテスト
 */
final class UserBillingOfficeTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingOffice $userBillingOffice;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'name' => '事業所テスト',
                'corporationName' => '事業所テスト',
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'tel' => '012-245-6789',
            ];
            $self->userBillingOffice = UserBillingOffice::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $office = Office::create([
            'name' => '事務所名',
            'corporationName' => '法人名',
            'addr' => new Addr(
                postcode: '164-0000',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: 'どこだろう',
                apartment: 'ハーモニータワー',
            ),
            'tel' => '090-0000-0000',
        ]);

        $this->assertModelStrictEquals(
            UserBillingOffice::create([
                'name' => '事務所名',
                'corporationName' => '法人名',
                'addr' => new Addr(
                    postcode: '164-0000',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: 'どこだろう',
                    apartment: 'ハーモニータワー',
                ),
                'tel' => '090-0000-0000',
            ]),
            UserBillingOffice::from($office)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'name' => ['name'],
            'corporationName' => ['corporationName'],
            'addr' => ['addr'],
            'tel' => ['tel'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingOffice->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBillingOffice);
        });
    }
}
