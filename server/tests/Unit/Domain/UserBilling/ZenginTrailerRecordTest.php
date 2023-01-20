<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginTrailerRecord;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\ZenginTrailerRecord} のテスト.
 */
final class ZenginTrailerRecordTest extends Test
{
    use UnitSupport;
    use ExamplesConsumer;

    protected ZenginTrailerRecord $zenginTrailerRecord;
    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'totalCount' => 123456,
                'totalAmount' => 123456789012,
                'succeededCount' => 0,
                'succeededAmount' => 0,
                'failedCount' => 0,
                'failedAmount' => 0,
            ];
            $self->zenginTrailerRecord = ZenginTrailerRecord::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return ZenginTrailerRecord', function (): void {
            $dataRecords = Seq::from(...$this->examples->withdrawalTransactions[0]->items)
                ->map(fn (WithdrawalTransactionItem $item) => $item->zenginRecord);

            $expected = ZenginTrailerRecord::create([
                'totalCount' => $dataRecords->count(),
                'totalAmount' => $dataRecords->map(fn (ZenginDataRecord $x) => $x->amount)->sum(),
                'succeededCount' => 0,
                'succeededAmount' => 0,
                'failedCount' => 0,
                'failedAmount' => 0,
            ]);
            $this->assertModelStrictEquals(
                $expected,
                ZenginTrailerRecord::from($dataRecords)
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
            'totalCount' => ['totalCount'],
            'totalAmount' => ['totalAmount'],
            'succeededCount' => ['succeededCount'],
            'succeededAmount' => ['succeededAmount'],
            'failedCount' => ['failedCount'],
            'failedAmount' => ['failedAmount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->zenginTrailerRecord->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }
}
