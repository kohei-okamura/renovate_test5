<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Statement CopayCoordination Update のテスト(Dws)
 * PUT /dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/copay-coordination
 */
class UpdateStatementCopayCoordinationCest extends Test
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
        $statement = $this->examples->dwsBillingStatements[6];

        $repository = $this->getStatementRepository();

        // 事前確認
        /** @var \Domain\Billing\DwsBillingStatement $before */
        $before = $repository->lookup($statement->id)->head();
        $aggregate = $before->aggregates[0];
        $I->assertNull($aggregate->coordinatedCopay);
        $I->assertSame($aggregate->cappedCopay, $aggregate->subtotalCopay);

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            ['amount' => 9300] + $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'id' => $statement->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}");
        $I->seeResponseJson($actual);
        $I->assertSame(DwsBillingStatus::ready()->value(), $actual['statement']['status']);
        $I->assertSame(DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(), $actual['statement']['copayCoordinationStatus']);
        // 金額
        $actualAggregate = $actual['statement']['aggregates'][0];
        $I->assertSame(9300, $actualAggregate['coordinatedCopay']);
        $I->assertSame(9300, $actualAggregate['subtotalCopay']);
    }

    /**
     * 初更新で登録されるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithNoCopayCoordination(ApiTester $I)
    {
        $I->wantTo('succeed API call with no CopayCoordination');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[7];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($this->examples->dwsBillingStatements[6])
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'id' => $statement->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}");
        $I->seeResponseJson($actual);
        $I->assertSame(DwsBillingStatus::ready()->value(), $actual['statement']['status']);
        $I->assertSame(DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(), $actual['statement']['copayCoordinationStatus']);
    }

    /**
     * 上限管理がない明細の更新が弾かれるテスト.
     *
     * @param \ApiTester $I
     */
    public function failedAPICallWithCopayCoordinationIsUnApplicable(ApiTester $I)
    {
        $I->wantTo('failed with CopayCoordination is unApplicable');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[8];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            ['result' => CopayCoordinationResult::appropriated()->value(), 'amount' => 0]
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['上限管理結果を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 明細のStatus が fixed() の時に更新ができないテスト.
     *
     * @param \ApiTester $I
     */
    public function failAPICallWithBadRequestWhenStatusIsFixed(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when status is fixed');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[9];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            ['result' => CopayCoordinationResult::appropriated()->value(), 'amount' => 0]
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['明細書を更新できません。']]]);
        $I->seeLogCount(0);
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
        $statement = $this->examples->dwsBillingStatements[6];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingStatement({$id}) not found.");
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingBundleId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$billingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingBundle({$billingBundleId}) not found");
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = $this->examples->dwsBillings[3]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = $this->examples->dwsBillings[1]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
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
        $statement = $this->examples->dwsBillingStatements[6];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param \Domain\Billing\DwsBillingStatement $param
     * @return array
     */
    private function defaultParam(DwsBillingStatement $param): array
    {
        return [
            'result' => $param->copayCoordination->result->value(),
            'amount' => $param->copayCoordination->amount,
        ];
    }

    /**
     * 障害福祉サービス:請求:明細書 Repository 取得.
     *
     * @return \Domain\Billing\DwsBillingStatementRepository
     */
    private function getStatementRepository(): DwsBillingStatementRepository
    {
        return app(DwsBillingStatementRepository::class);
    }
}
