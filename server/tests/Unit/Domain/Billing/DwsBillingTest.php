<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingFile;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Common\Prefecture;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBilling} のテスト.
 */
final class DwsBillingTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBilling $dwsBilling;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'office' => DwsBillingOffice::create([
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
                ]),
                'transactedIn' => Carbon::today(),
                'files' => [
                    new DwsBillingFile(
                        name: 'サービス提供実績記録票_藤沢_202012.csv',
                        path: 'attachments/xyz.csv',
                        token: str_repeat('x', 60),
                        mimeType: MimeType::csv(),
                        createdAt: Carbon::create(2009, 10, 10, 19, 11, 19),
                        downloadedAt: Carbon::create(2006, 12, 13, 3, 55, 31),
                    ),
                ],
                'status' => DwsBillingStatus::checking(),
                'fixedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->dwsBilling = DwsBilling::create($self->values);
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
            'office' => ['office'],
            'transactedIn' => ['transactedIn'],
            'files' => ['files'],
            'status' => ['status'],
            'fixedAt' => ['fixedAt'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBilling->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBilling);
        });
    }
}
