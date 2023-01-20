<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OrganizationSetting;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OrganizationSetting update のテスト.
 * PUT /setting
 */
class UpdateOrganizationSettingCest extends OrganizationSettingTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

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
        $I->sendPUT('/setting', $this->domainToArray($this->defaultParam()));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業者別設定が更新されました', [
            'id' => '*',
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET('/setting');
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $I->sendPUT('/setting', $this->domainToArray($this->defaultParam()));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return ['bankingClientCode' => '9999999999'];
    }
}
