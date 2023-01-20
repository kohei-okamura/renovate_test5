<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Domain\UserBilling\ZenginHeaderRecord;
use Domain\UserBilling\ZenginRecord;
use Domain\UserBilling\ZenginTrailerRecord;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\ZenginRecord} のテスト
 */
class ZenginRecordTest extends Test
{
    use UnitSupport;
    use ExamplesConsumer;
    use MatchesSnapshots;

    protected ZenginRecord $zenginRecord;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ZenginRecordTest $self): void {
            $self->values = [
                'header' => ZenginHeaderRecord::create(),
                'data' => [ZenginDataRecord::create()],
                'trailer' => ZenginTrailerRecord::create(),
            ];
            $self->zenginRecord = ZenginRecord::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return ZenginRecord', function (): void {
            $withdrawalTransaction = $this->examples->withdrawalTransactions[0];
            $bankingClientName = 'ユースタイルラボラトリー（カ';
            $deductedOn = Carbon::now()->lastOfMonth();
            $dataRecords = Seq::from(...$withdrawalTransaction->items)
                ->map(fn (WithdrawalTransactionItem $item) => $item->zenginRecord);

            $expected = ZenginRecord::create([
                'header' => ZenginHeaderRecord::from($withdrawalTransaction, $bankingClientName, $deductedOn),
                'data' => $dataRecords->toArray(),
                'trailer' => ZenginTrailerRecord::from($dataRecords),
            ]);
            $this->assertModelStrictEquals(
                $expected,
                ZenginRecord::from($withdrawalTransaction, $bankingClientName, $deductedOn)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toZenginRecordString(): void
    {
        $this->should('return string of ZenginRecord', function (): void {
            $zenginRecord = ZenginRecord::create([
                'header' => ZenginHeaderRecord::create([
                    'bankingClientCode' => '1234567890',
                    'bankingClientName' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ(ｶ',
                    'deductedOn' => Carbon::parse('2020-10-10'),
                ]),
                'data' => [
                    ZenginDataRecord::create([
                        'bankCode' => '0005',
                        'bankBranchCode' => '798',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '1234567',
                        'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                        'amount' => 198000,
                        'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                        'clientNumber' => '12345678901234567890',
                        'withdrawalResultCode' => WithdrawalResultCode::done(),
                    ]),
                    ZenginDataRecord::create([
                        'bankCode' => '0006',
                        'bankBranchCode' => '123',
                        'bankAccountType' => BankAccountType::currentDeposit(),
                        'bankAccountNumber' => '7654321',
                        'bankAccountHolder' => 'ﾔﾏﾀﾞ ﾊﾅｺ',
                        'amount' => 72000,
                        'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                        'clientNumber' => '09876543210987654321',
                        'withdrawalResultCode' => WithdrawalResultCode::done(),
                    ]),
                ],
                'trailer' => ZenginTrailerRecord::create([
                    'totalCount' => 123456,
                    'totalAmount' => 123456789012,
                    'succeededCount' => 0,
                    'succeededAmount' => 0,
                    'failedCount' => 0,
                    'failedAmount' => 0,
                ]),
            ]);

            $this->assertMatchesTextSnapshot($zenginRecord->toZenginRecordString());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'header' => ['header'],
            'data' => ['data'],
            'trailer' => ['trailer'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->zenginRecord->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }
}
