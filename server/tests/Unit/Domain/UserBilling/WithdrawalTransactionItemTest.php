<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\WithdrawalTransactionItem} のテスト.
 */
final class WithdrawalTransactionItemTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    protected array $values = [];
    private WithdrawalTransactionItem $withdrawalTransactionItem;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'userBillingIds' => [1, 2],
                'zenginRecord' => ZenginDataRecord::create(),
            ];
            $self->withdrawalTransactionItem = WithdrawalTransactionItem::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'userBillingIds' => ['userBillingIds'],
            'zenginRecord' => ['zenginRecord'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->withdrawalTransactionItem->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->withdrawalTransactionItem);
        });
    }
}
