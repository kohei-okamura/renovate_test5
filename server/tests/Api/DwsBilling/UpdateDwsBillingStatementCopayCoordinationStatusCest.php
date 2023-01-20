<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * StatementCopayCoordinationStatus Update のテスト(Dws).
 * PUT /dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/copay-coordination-status
 */
class UpdateDwsBillingStatementCopayCoordinationStatusCest extends Test
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
        $I->markTestSkipped('存在しないサービス詳細が含まれており外部APIで動かないためskip');
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[11];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination-status",
            $this->defaultParam(DwsBillingStatementCopayCoordinationStatus::unclaimable())
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'id' => $statement->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        // Responseの検証
        $I->sendGET("/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}");
        $latest = $I->grabResponseArray();

        $I->assertEquals($latest, $actual);
    }

    /**
     * 「上限管理区分」の変更前の状態が「未作成」でない場合に更新ができないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsNotUncreated(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when copayCoordinationStatus is not uncreated');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[6];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination-status",
            $this->defaultParam(DwsBillingStatementCopayCoordinationStatus::uncreated())
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['status' => ['上限管理区分を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 「上限管理区分」の変更後の状態が「不要（サービス提供なし）」でない場合に更新ができないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsNotUnclaimable(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when copayCoordinationStatus is not unclaimable');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[11];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/copay-coordination-status",
            $this->defaultParam(DwsBillingStatementCopayCoordinationStatus::unapplicable())
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['status' => ['上限管理区分を更新できません。']]]);
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
        $statement = $this->examples->dwsBillingStatements[11];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$id}/copay-coordination-status",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingStatement({$id}) not found.");
    }

    /**
     * 入力値の組み立て.
     *
     * @param \Domain\Billing\DwsBillingStatement|\Domain\Billing\DwsBillingStatementCopayCoordinationStatus $param
     * @return array
     */
    private function defaultParam($param): array
    {
        if ($param instanceof DwsBillingStatement) {
            $copayCoordinationStatus = $param->copayCoordinationStatus;
        } else {
            $copayCoordinationStatus = $param;
        }

        return ['status' => $copayCoordinationStatus->value()];
    }
}
