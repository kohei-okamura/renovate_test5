<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayListPdf;
use Domain\Billing\CopayListSource;
use Domain\Billing\DwsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Pdf\PdfSupport;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\CopayListPdf} のテスト.
 */
final class CopayListPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use PdfSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_construct(): void
    {
        $this->should('create CopayListPdf', function (): void {
            $actual = new CopayListPdf(
                issuedOn: Carbon::parse('2020-10-10')->toJapaneseDate(),
                copayCoordinationOfficeName: '上限管理事業所のなまえ',
                officeCode: '1234567890',
                officeAddr: new Addr(
                    postcode: '164-0012',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '本町1-32-2',
                    apartment: 'ハーモニータワー18F',
                ),
                officeTel: '03-1234-5678',
                officeName: '事業所のなまえ',
                providedIn: self::localized(Carbon::parse('2020-10-10')),
                items: []
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return CopayListPdf', function (): void {
            $billing = $this->examples->dwsBillings[0]->copy([
                'office' => DwsBillingOffice::create([
                    'officeId' => 1,
                    'code' => '1234567890',
                    'name' => '事業所名',
                    'abbr' => '事業所名略称',
                    'addr' => new Addr(
                        postcode: '739-0604',
                        prefecture: Prefecture::hiroshima(),
                        city: '大竹市',
                        street: '北栄1-13-11',
                        apartment: '北栄荘411',
                    ),
                    'tel' => '03-1234-5678',
                ]),
            ]);
            $bundles = Seq::from($this->examples->dwsBillingBundles[0], $this->examples->dwsBillingBundles[1]);
            $user1 = $this->examples->dwsBillingStatements[0]->user->copy([
                'dwsNumber' => '1234567890',
            ]);
            $statement1 = $this->examples->dwsBillingStatements[0]->copy([
                'user' => $user1,
                'totalFee' => 100000,
                'totalCappedCopay' => 9300,
                'totalAdjustedCopay' => 9300,
            ]);
            $user2 = $this->examples->dwsBillingStatements[4]->user->copy([
                'dwsNumber' => '5432106789',
            ]);
            $statement2 = $this->examples->dwsBillingStatements[4]->copy([
                'user' => $user2,
                'totalFee' => 150000,
                'totalCappedCopay' => 37200,
                'totalAdjustedCopay' => 37200,
            ]);
            $sources = Seq::from(
                new CopayListSource(
                    copayCoordinationOfficeName: '利用者の多い上限管理事業所名',
                    statements: [
                        $statement1,
                        $statement1,
                        $statement1,
                        $statement1,
                        $statement1,
                        $statement2,
                        $statement2,
                        $statement2,
                        $statement2,
                        $statement2,
                        $statement2,
                    ]
                ),
                new CopayListSource(
                    copayCoordinationOfficeName: '利用者の少ない上限管理事業所名',
                    statements: [
                        $statement1,
                        $statement2,
                    ]
                ),
            );
            $issuedOn = Carbon::now();

            $actual = CopayListPdf::from($billing, $bundles, $sources, $issuedOn);
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should(
            'throw invalidArgumentException if sources do not contain bundle for the statement',
            function (): void {
                $billing = $this->examples->dwsBillings[0]->copy([
                    'office' => DwsBillingOffice::create([
                        'officeId' => 1,
                        'code' => '1234567890',
                        'name' => '事業所名',
                        'abbr' => '事業所名略称',
                        'addr' => new Addr(
                            postcode: '739-0604',
                            prefecture: Prefecture::hiroshima(),
                            city: '大竹市',
                            street: '北栄1-13-11',
                            apartment: '北栄荘411',
                        ),
                        'tel' => '03-1234-5678',
                    ]),
                ]);
                $bundles = Seq::from($this->examples->dwsBillingBundles[0]);
                $statement1 = $this->examples->dwsBillingStatements[0];
                $statement2 = $this->examples->dwsBillingStatements[4];
                $sources = Seq::from(
                    new CopayListSource(
                        copayCoordinationOfficeName: '利用者の多い上限管理事業所名',
                        statements: [
                            $statement1,
                            $statement1,
                            $statement1,
                            $statement1,
                            $statement1,
                            $statement2,
                            $statement2,
                            $statement2,
                            $statement2,
                            $statement2,
                            $statement2,
                        ]
                    ),
                    new CopayListSource(
                        copayCoordinationOfficeName: '利用者の少ない上限管理事業所名',
                        statements: [
                            $statement1,
                            $statement2,
                        ]
                    ),
                );
                $issuedOn = Carbon::now();

                $this->assertThrows(
                    InvalidArgumentException::class,
                    function () use ($billing, $bundles, $sources, $issuedOn): void {
                        CopayListPdf::from($billing, $bundles, $sources, $issuedOn)->computed();
                    }
                );
            }
        );
    }
}
