<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserBilling getIndex のテスト
 * GET /user-billings
 */
class GetIndexUserBillingCest extends UserBillingTest
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

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('user-billings');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * ソートテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortById(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (UserBilling $x): int => $x->id)
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('user-billings', ['sortBy' => 'id']);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * 事業所を指定したフィルタテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithOfficeIdFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API Call with office id filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->userBillings[0]->officeId;

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->officeId === $officeId)
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("user-billings?officeId={$officeId}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * サービス提供年月を指定したフィルタテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithProvidedInFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API Call with provided_in filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-04';

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->providedIn->format('Y-m') === $providedIn)
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("user-billings?providedIn={$providedIn}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 請求年月を指定したフィルタテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithIssuedInFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API Call with issued_in filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $issuedIn = '2020-05';

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->issuedOn->format('Y-m') === $issuedIn)
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("user-billings?issuedIn={$issuedIn}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 支払方法、請求結果を指定したフィルタテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithPaymentMethodAndResultFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API Call with payment method and result filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $paymentMethod = PaymentMethod::withdrawal();
        $result = UserBillingResult::paid();

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod === $paymentMethod)
            ->filter(fn (UserBilling $x): bool => $x->result === $result)
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        // 支払方法、請求結果は同時に指定しても確認可能なので一緒にやる
        $I->sendGET("user-billings?paymentMethod={$paymentMethod->value()}&result={$result->value()}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 認可された事業所だけ取得できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithOnlyUsersWhoBelongToTheAuthorizedOffice(ApiTester $I)
    {
        $I->wantTo('succeed api call with only users who belong to the authorized office');

        $staff = $this->examples->staffs[28]; // 事業所管理者
        $I->actingAs($staff);

        $expected = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x) => in_array($x->officeId, $staff->officeIds, true))
            ->sortBy(fn (UserBilling $x): string => $this->replace_to_seion($x->user->name->phoneticDisplayName))
            ->map(fn (UserBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('user-billings');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('user-billings');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
