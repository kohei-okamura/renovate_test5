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
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Common\Carbon;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * CopayCoordination update のテスト.
 * PUT /dws-billings/{billingId}/bundles/{bundleId}/copay-coordinations/{id}
 */
class UpdateCopayCoordinationCest extends Test
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
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[8]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[8]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者負担上限額管理結果票が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $actualBillingInfo = $I->grabResponseArray();

        $items = $this->examples->dwsBillingCopayCoordinations[8]->items;
        $expectedCopayCoordination = $this->examples->dwsBillingCopayCoordinations[8]->copy([
            'result' => CopayCoordinationResult::appropriated()->value(),
            'items' => [
                $items[0]->copy([
                    'office' => DwsBillingOffice::from($this->examples->offices[0]), // 上限管理事業所
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 37200,
                        'coordinatedCopay' => 37200, // 受給者証のcopayLimitと同じ
                    ],
                ]),
                $items[1]->copy([
                    'office' => DwsBillingOffice::from($this->examples->offices[2]),
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 0,
                    ],
                ]),
            ],
            // item1 + item2
            'total' => DwsBillingCopayCoordinationPayment::create([
                'fee' => 300000,
                'copay' => 47200,
                'coordinatedCopay' => 37200,
            ]),
            'updatedAt' => Carbon::now(),
        ]);

        $I->assertSame($this->domainToArray($expectedCopayCoordination), $actualBillingInfo['copayCoordination']);
    }

    /**
     * 明細書の「上限管理区分」が「不要（上限管理なし）」の場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsUnapplicable(ApiTester $I)
    {
        $I->wantTo('fail with bad request when copay coordination status is unapplicable');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[4]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            ['userId' => $this->examples->dwsBillingCopayCoordinations[5]->user->userId] + $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['利用者負担上限額管理結果票を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 明細書の「上限管理区分」が「不要（サービス提供なし）」の場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsUnclaimable(ApiTester $I)
    {
        $I->wantTo('fail with bad request when copay coordination status is unclaimable');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[6]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            ['userId' => $this->examples->dwsBillingCopayCoordinations[5]->user->userId] + $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['利用者負担上限額管理結果票を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 明細書の状態が「確定済み」の場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenStatusOfStatementIsFixed(ApiTester $I)
    {
        $I->wantTo('fail with bad request when status of statement is fixed');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[5]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            ['userId' => $this->examples->dwsBillingCopayCoordinations[5]->user->userId] + $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['利用者負担上限額管理結果票を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 上限管理票IDが存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenCopayCoordinationIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when CopayCoordinationID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "CopayCoordination({$id}) not found");
    }

    /**
     * 請求が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when BillingId not exists.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = self::NOT_EXISTING_ID;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[0]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
    }

    /**
     * 請求単位が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenBundleIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when BundleId not exists.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = self::NOT_EXISTING_ID;
        $id = $this->examples->dwsBillingCopayCoordinations[0]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingBundle({$bundleId}) not found");
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
        $billingId = $this->examples->dwsBillings[0]->id;
        $bundleId = $this->examples->dwsBillingBundles[0]->id;
        $id = $this->examples->dwsBillingCopayCoordinations[0]->id;

        $I->sendPUT(
            "dws-billings/{$billingId}/bundles/{$bundleId}/copay-coordinations/{$id}",
            $this->buildParams()
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータを組み立てる.
     *
     * @return array
     */
    private function buildParams(): array
    {
        return [
            'userId' => $this->examples->users[18]->id,
            'result' => CopayCoordinationResult::appropriated()->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => [
                [
                    'officeId' => $this->examples->offices[0]->id, // 上限管理事業所
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 37200,
                        'coordinatedCopay' => 37200, // 受給者証のcopayLimitと同じ
                    ],
                ],
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 0,
                    ],
                ],
            ],
        ];
    }
}
