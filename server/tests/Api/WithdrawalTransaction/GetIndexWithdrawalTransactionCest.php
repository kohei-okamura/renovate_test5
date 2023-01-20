<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\WithdrawalTransaction;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\UserBilling\WithdrawalTransaction;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * WithdrawalTransaction getIndex のテスト
 * GET /withdrawal-transactions
 */
class GetIndexWithdrawalTransactionCest extends WithdrawalTransactionTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $expected = Seq::fromArray($this->examples->withdrawalTransactions)
            ->filter(fn (WithdrawalTransaction $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (WithdrawalTransaction $x): int => $x->id)
            ->map(fn (WithdrawalTransaction $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('withdrawal-transactions');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * フィルタ指定テスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWhenSpecifyFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->withdrawalTransactions[1]->createdAt;
        $start = Carbon::parse('2020-10-10');
        $end = Carbon::parse('2020-10-11');
        $expected = Seq::fromArray($this->examples->withdrawalTransactions)
            ->filter(fn (WithdrawalTransaction $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (WithdrawalTransaction $x): bool => $x->createdAt->gte($start->startOfDay()) && $x->createdAt->lte($end->endOfDay()))
            ->sortBy(fn (WithdrawalTransaction $x): int => $x->id)
            ->map(fn (WithdrawalTransaction $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('withdrawal-transactions', compact('start', 'end'));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('withdrawal-transactions');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
