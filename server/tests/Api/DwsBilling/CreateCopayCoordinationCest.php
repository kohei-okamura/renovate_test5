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
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * CopayCoordination Create のテスト.
 * POST /dws-billings/{billingId}/bundles/{bundleId}/copay-coordinations
 */
final class CreateCopayCoordinationCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * 正常なパラメータであれば API の呼び出しに成功する.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICall(ApiTester $I): void
    {
        $I->wantTo('正常なパラメータであれば API の呼び出しに成功する');

        // GIVEN
        $user = $this->examples->users[2];
        $billing = $this->examples->dwsBillings[0];
        $bundle = $this->examples->dwsBillingBundles[0];
        $statement = $this->examples->dwsBillingStatements[10]; // 利用者負担上限額管理結果票に対応する明細書
        // DB のユニーク制約で引っかかるため、とりあえず請求単位に紐づく上限管理結果票を全部消しておく
        /** @var \Domain\Billing\DwsBillingCopayCoordinationRepository $copayCoordinationRepository */
        $copayCoordinationRepository = app(DwsBillingCopayCoordinationRepository::class);
        $copayCoordinationRepository->lookupByBundleId($bundle->id)
            ->values()
            ->flatten()
            ->each(fn (DwsBillingCopayCoordination $x) => $copayCoordinationRepository->remove($x));
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        // WHEN
        $I->sendPOST("dws-billings/{$billing->id}/bundles/{$bundle->id}/copay-coordinations", [
            'userId' => $user->id,
            'result' => CopayCoordinationResult::notCoordinated()->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => [
                [
                    'officeId' => $this->examples->offices[0]->id,
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 20000,
                        'coordinatedCopay' => 15000,
                    ],
                ],
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 5000,
                    ],
                ],
            ],
        ]);

        // THEN
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson($this->domainToArray([
            'billing' => $billing,
            'bundle' => $bundle,
            'copayCoordination' => DwsBillingCopayCoordination::create([
                'id' => count($this->examples->dwsBillingCopayCoordinations) + 1,
                'dwsBillingId' => $billing->id,
                'dwsBillingBundleId' => $bundle->id,
                'office' => DwsBillingOffice::from($this->examples->offices[0]),
                'user' => DwsBillingUser::from($user, $this->examples->dwsCertifications[18]),
                'result' => CopayCoordinationResult::notCoordinated(),
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
                'items' => [
                    DwsBillingCopayCoordinationPayment::create([
                        'fee' => 200000,
                        'copay' => 20000,
                        'coordinatedCopay' => 15000,
                    ]),
                    DwsBillingCopayCoordinationPayment::create([
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 5000,
                    ]),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 300000,
                    'copay' => 30000,
                    'coordinatedCopay' => 20000,
                ]),
                'status' => DwsBillingStatus::ready(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ]));
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者負担上限額管理結果票が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        //
        // 明細書が更新されていることを検証する
        //
        $I->sendGet("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");
        $actualStatementInfo = $I->grabResponseArray();
        $expectedStatement = $statement->copy([
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking(),
            'updatedAt' => Carbon::now(),
        ]);
        $I->assertSame($this->domainToArray($expectedStatement), $actualStatementInfo['statement']);
    }

    /**
     * 請求が存在しない場合は 404 Not Found となる.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBillingIdNotExists(ApiTester $I): void
    {
        $I->wantTo('請求が存在しない場合は 404 Not Found となる');

        // GIVEN
        $user = $this->examples->users[2];
        $bundle = $this->examples->dwsBillingBundles[0];
        $I->actingAs($this->examples->staffs[0]);

        // WHEN
        $I->sendPOST("dws-billings/99999999/bundles/{$bundle->id}/copay-coordinations", [
            'userId' => $user->id,
            'result' => CopayCoordinationResult::appropriated()->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => [
                [
                    'officeId' => $this->examples->offices[0]->id,
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 20000,
                        'coordinatedCopay' => 15000,
                    ],
                ],
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 5000,
                    ],
                ],
            ],
        ]);

        // THEN
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'DwsBilling(99999999) not found');
    }

    /**
     * 請求単位が存在しない場合は 404 Not Found となる.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBundleIdNotExists(ApiTester $I): void
    {
        $I->wantTo('請求単位が存在しない場合は 404 Not Found となる');

        // GIVEN
        $user = $this->examples->users[2];
        $billing = $this->examples->dwsBillings[0];
        $I->actingAs($this->examples->staffs[0]);

        // WHEN
        $I->sendPOST("dws-billings/{$billing->id}/bundles/99999999/copay-coordinations", [
            'userId' => $user->id,
            'result' => CopayCoordinationResult::appropriated()->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => [
                [
                    'officeId' => $this->examples->offices[0]->id,
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 20000,
                        'coordinatedCopay' => 15000,
                    ],
                ],
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 5000,
                    ],
                ],
            ],
        ]);

        // THEN
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'DwsBillingBundle(99999999) not found');
    }

    /**
     * 必要な権限を持たないスタッフによるリクエストは 403 Forbidden となる.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('必要な権限を持たないスタッフによるリクエストは 403 Forbidden となる');

        // GIVEN
        $user = $this->examples->users[2];
        $billing = $this->examples->dwsBillings[0];
        $bundle = $this->examples->dwsBillingBundles[0];
        $I->actingAs($this->examples->staffs[29]); // ヘルパー権限のスタッフ

        // WHEN
        $I->sendPOST("dws-billings/{$billing->id}/bundles/{$bundle->id}/copay-coordinations", [
            'userId' => $user->id,
            'result' => CopayCoordinationResult::appropriated()->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => [
                [
                    'officeId' => $this->examples->offices[0]->id,
                    'subtotal' => [
                        'fee' => 200000,
                        'copay' => 20000,
                        'coordinatedCopay' => 15000,
                    ],
                ],
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'subtotal' => [
                        'fee' => 100000,
                        'copay' => 10000,
                        'coordinatedCopay' => 5000,
                    ],
                ],
            ],
        ]);

        // THEN
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
