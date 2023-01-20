<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Shift cancelのテスト.
 * POST /shifts/{id}/cancel
 */
class CancelShiftCest extends ShiftTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API Call.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->shifts[0]->id; // キャンセルされていないShift
        $param = ['reason' => 'キャンセル理由'];

        $I->sendPOST("/shifts/{$id}/cancel", $param);

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務シフトがキャンセルされました', [
            'id' => '', 'organizationId' => $staff->organizationId, 'staffId' => $staff->id,
        ]);
        // データ確認
        $I->sendGET("/shifts/{$id}");
        $actual = $I->grabResponseArray();
        $I->assertTrue($actual['shift']['isCanceled']);
    }

    /**
     * キャンセル済みのIDで400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsCanceled(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is canceled.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->shifts[3]->id;
        $param = ['reason' => 'キャンセル理由'];

        $I->sendPOST("/shifts/{$id}/cancel", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDで400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsInvalid(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is invalid.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $param = ['reason' => 'キャンセル理由'];

        $I->sendPOST("/shifts/{$id}/cancel", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * IDが文字列で404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = 'String';
        $param = ['reason' => 'キャンセル理由'];

        $I->sendPOST("/shifts/{$id}/cancel", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
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
        $id = $this->examples->shifts[0]->id;
        $param = ['reason' => 'キャンセル理由'];

        $I->sendPOST("/shifts/{$id}/cancel", $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 開始日が過去の勤務シフトは編集できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenStartDateTimeIsPast(ApiTester $I)
    {
        $I->wantTo('fail when start date time is past');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST("/shifts/{$this->examples->shifts[11]->id}/cancel", ['reason' => 'キャンセル理由']);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['過去の勤務シフトはキャンセルできません。']]]);
        $I->seeLogCount(0);
    }
}
