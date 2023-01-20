<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordination} のテスト.
 */
final class DwsBillingCopayCoordinationTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingCopayCoordination $dwsBillingCopayCoordination;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'id' => 1,
                'dwsBillingId' => 3,
                'dwsBillingBundleId' => 1,
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
                'user' => DwsBillingUser::create([
                    'userId' => $self->examples->users[0]->id,
                    'dwsCertificationId' => $self->examples->dwsCertifications[9]->id,
                    'dwsNumber' => '0123456789',
                    'name' => new StructuredName(
                        familyName: '土屋',
                        givenName: '花子',
                        phoneticFamilyName: 'ツチヤ',
                        phoneticGivenName: 'ハナコ',
                    ),
                    'childName' => new StructuredName(
                        familyName: '土屋',
                        givenName: '太郎',
                        phoneticFamilyName: 'ツチヤ',
                        phoneticGivenName: 'タロウ',
                    ),
                    'copayLimit' => 10000,
                ]),
                'result' => CopayCoordinationResult::appropriated(),
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
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
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 10000,
                            'copay' => 10000,
                            'coordinatedCopay' => 10000,
                        ]),
                    ]),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 10000,
                    'copay' => 10000,
                    'coordinatedCopay' => 10000,
                ]),
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
                'status' => DwsBillingStatus::checking(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->dwsBillingCopayCoordination = DwsBillingCopayCoordination::create($self->values);
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
            'dwsBillingBundleId' => ['dwsBillingBundleId'],
            'office' => ['office'],
            'user' => ['user'],
            'result' => ['result'],
            'exchangeAim' => ['exchangeAim'],
            'items' => ['items'],
            'total' => ['total'],
            'status' => ['status'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingCopayCoordination->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingCopayCoordination);
        });
    }
}
