<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\Carbon;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingOtherItem;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\UserBillingUser;
use Domain\UserBilling\WithdrawalResultCode;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBilling} のテスト
 */
class UserBillingTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBilling $userBilling;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserBillingTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'userId' => 1,
                'officeId' => 1,
                'user' => UserBillingUser::create(),
                'office' => UserBillingOffice::create(),
                'dwsItem' => UserBillingDwsItem::create(),
                'ltcsItem' => UserBillingLtcsItem::create(),
                'otherItems' => [UserBillingOtherItem::create()],
                'result' => UserBillingResult::paid(),
                'carriedOverAmount' => 1000,
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'providedIn' => Carbon::create(),
                'issuedOn' => Carbon::create(),
                'depositedAt' => Carbon::create(),
                'transactedAt' => Carbon::create(),
                'deductedOn' => Carbon::create(),
                'dueDate' => Carbon::create(),
                'createdAt' => Carbon::create(),
                'updatedAt' => Carbon::create(),
            ];
            $self->userBilling = UserBilling::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_totalAmount(): void
    {
        $this->should('return amount', function (): void {
            $userBilling = UserBilling::create([
                'dwsItem' => UserBillingDwsItem::create(['copayWithTax' => 30]),
                'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 40]),
                'otherItems' => [
                    UserBillingOtherItem::create(['copayWithTax' => 20]),
                    UserBillingOtherItem::create(['copayWithTax' => 20]),
                ],
                'carriedOverAmount' => -10,
            ]);
            $this->assertSame(100, $userBilling->totalAmount);
        });
        $this->should('return amount when each items are empty', function (): void {
            $userBilling = UserBilling::create([
                'dwsItem' => null,
                'ltcsItem' => null,
                'otherItems' => [],
                'carriedOverAmount' => -10,
            ]);
            $this->assertSame(-10, $userBilling->totalAmount);
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
            'userId' => ['userId'],
            'officeId' => ['officeId'],
            'user' => ['user'],
            'office' => ['office'],
            'dwsItem' => ['dwsItem'],
            'ltcsItem' => ['ltcsItem'],
            'otherItems' => ['otherItems'],
            'result' => ['result'],
            'carriedOverAmount' => ['carriedOverAmount'],
            'withdrawalResultCode' => ['withdrawalResultCode'],
            'providedIn' => ['providedIn'],
            'issuedOn' => ['issuedOn'],
            'depositedAt' => ['depositedAt'],
            'transactedAt' => ['transactedAt'],
            'deductedOn' => ['deductedOn'],
            'dueDate' => ['dueDate'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBilling->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBilling);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_computedAttrs(): void
    {
        $examples = [
            'totalAmount' => ['totalAmount'],
        ];
        $this->should(
            'have specified computed attribute',
            function (string $key): void {
                $this->assertNotNull($this->userBilling->get($key));
            },
            compact('examples')
        );
    }
}
