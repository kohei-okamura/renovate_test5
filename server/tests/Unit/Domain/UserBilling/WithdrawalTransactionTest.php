<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\Carbon;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\WithdrawalTransaction} のテスト.
 */
final class WithdrawalTransactionTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;
    protected array $values = [];

    private WithdrawalTransaction $withdrawalTransaction;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'items' => [WithdrawalTransactionItem::create(), WithdrawalTransactionItem::create()],
                'deductedOn' => Carbon::create(),
                'downloadedAt' => Carbon::create(),
                'createdAt' => Carbon::create(),
                'updatedAt' => Carbon::create(),
            ];
            $self->withdrawalTransaction = WithdrawalTransaction::create($self->values);
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
            'organizationId' => ['organizationId'],
            'items' => ['items'],
            'deductedOn' => ['deductedOn'],
            'downloadedAt' => ['downloadedAt'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->withdrawalTransaction->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->withdrawalTransaction);
        });
    }
}
