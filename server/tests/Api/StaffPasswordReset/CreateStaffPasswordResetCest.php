<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\StaffPasswordReset;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Staff\Staff;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * StaffPasswordReset create のテスト
 * POST /password-resets
 */
class CreateStaffPasswordResetCest extends StaffPasswordResetTest
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
        $param = $this->defaultParam($staff);

        $I->sendPOST('/password-resets', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'スタッフパスワード再設定が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => '',
        ]);
    }

    /**
     * パラメータの組み立て.
     *
     * @param Staff $staff
     * @return array
     */
    private function defaultParam(Staff $staff): array
    {
        return ['email' => $staff->email];
    }
}
