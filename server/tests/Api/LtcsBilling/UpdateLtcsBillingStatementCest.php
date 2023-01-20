<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsServiceDivisionCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Statement update のテスト(Ltcs).
 * PUT /ltcs-billings/{billingId}/bundles/{bundleId}/statements/{id}
 */
final class UpdateLtcsBillingStatementCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];

        $I->sendPUT(
            "/ltcs-billings/{$statement->billingId}/bundles/{$statement->bundleId}/statements/{$statement->id}",
            $this->defaultParam(LtcsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：明細書が更新されました', [
            'id' => $statement->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/ltcs-billings/{$statement->billingId}/bundles/{$statement->bundleId}/statements/{$id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingStatement({$id}) not found.");
    }

    /**
     * 請求IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];
        $billingId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/ltcs-billings/{$billingId}/bundles/{$statement->bundleId}/statements/{$statement->id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$billingId}) not found.");
    }

    /**
     * 請求単位IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingBundleIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when BillingBundle ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];
        $billingBundleId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/ltcs-billings/{$statement->billingId}/bundles/{$billingBundleId}/statements/{$statement->id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingBundle({$billingBundleId}) not found.");
    }

    /**
     * 請求IDが同じ事業者に存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID not in Organization.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];
        $billingId = $this->examples->ltcsBillings[3]->id;

        $I->sendPUT(
            "/ltcs-billings/{$billingId}/bundles/{$statement->bundleId}/statements/{$statement->id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingBundle({$statement->bundleId}) not found.");
    }

    /**
     * アクセス可能なOfficeの請求IDでない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID is not in accessible Office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];
        $billingId = $this->examples->ltcsBillings[1]->id;

        $I->sendPUT(
            "/ltcs-billings/{$billingId}/bundles/{$statement->bundleId}/statements/{$statement->id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingBundle({$statement->bundleId}) not found.");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithForbiddenWhenNotHavePermission(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when not have Permission.');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $statement = $this->examples->ltcsBillingStatements[0];

        $I->sendPUT(
            "/ltcs-billings/{$statement->billingId}/bundles/{$statement->bundleId}/statements/{$statement->id}",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return [
            'aggregates' => [
                [
                    'serviceDivisionCode' => LtcsServiceDivisionCode::homeVisitLongTermCare()->value(),
                    'plannedScore' => 0,
                ],
            ],
        ];
    }
}
