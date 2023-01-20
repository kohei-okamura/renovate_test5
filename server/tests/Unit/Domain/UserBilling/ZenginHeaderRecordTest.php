<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\Carbon;
use Domain\UserBilling\ZenginHeaderRecord;
use Illuminate\Support\Arr;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\ZenginHeaderRecord} のテスト.
 */
final class ZenginHeaderRecordTest extends Test
{
    use UnitSupport;
    use ExamplesConsumer;

    protected ZenginHeaderRecord $zenginHeaderRecord;
    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'bankingClientCode' => '1234567890',
                'bankingClientName' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ(ｶ',
                'deductedOn' => Carbon::now(),
            ];
            $self->zenginHeaderRecord = ZenginHeaderRecord::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return ZenginHeaderRecord', function (): void {
            $withdrawalTransaction = $this->examples->withdrawalTransactions[0];
            $bankingClientName = 'ユースタイルラボラトリーカブシキガイシヤ';
            $deductedOn = Carbon::now()->lastOfMonth();

            $expected = ZenginHeaderRecord::create([
                'bankingClientCode' => mb_substr($withdrawalTransaction->items[0]->zenginRecord->clientNumber, 0, 10),
                'bankingClientName' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰｶﾌﾞｼｷｶﾞｲｼﾔ',
                'deductedOn' => $deductedOn,
            ]);
            $this->assertModelStrictEquals(
                $expected,
                ZenginHeaderRecord::from($withdrawalTransaction, $bankingClientName, $deductedOn)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'bankingClientCode' => ['bankingClientCode'],
            'bankingClientName' => ['bankingClientName'],
            'deductedOn' => ['deductedOn'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->zenginHeaderRecord->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }
}
