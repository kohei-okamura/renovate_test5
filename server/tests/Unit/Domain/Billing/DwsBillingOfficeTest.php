<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingOffice} のテスト.
 */
final class DwsBillingOfficeTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingOffice $dwsBillingOffice;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'officeId' => $self->examples->offices[0]->id,
                'code' => '123456',
                'name' => '事業所1',
                'abbr' => '事業1',
                'addr' => new Addr(
                    postcode: '739-0604',
                    prefecture: Prefecture::hiroshima(),
                    city: '大竹市',
                    street: '北栄1-13-11',
                    apartment: '北栄荘411',
                ),
                'tel' => '090-3169-6661',
            ];
            $self->dwsBillingOffice = DwsBillingOffice::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'officeId' => ['officeId'],
            'code' => ['code'],
            'name' => ['name'],
            'abbr' => ['abbr'],
            'addr' => ['addr'],
            'tel' => ['tel'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingOffice->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingOffice);
        });
    }
}
