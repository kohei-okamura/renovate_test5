<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Staff\Staff;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staffs update のテスト.
 * PUT /staffs/{id}
 */
final class UpdateStaffCest extends StaffTest
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
        $updateStaff = $this->examples->staffs[2];
        $param = ['officeGroupIds' => [1]] + $this->buildParam($updateStaff);

        $I->sendPUT("staffs/{$updateStaff->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'スタッフ情報が更新されました', [
            'id' => $updateStaff->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("staffs/{$updateStaff->id}", $param);
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        $I->assertEquals($expected, $actual);
    }

    /**
     * 指定した E-mail アドレスを使用しているのが自分自身の時は正常終了するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedIfEmailAddressIsUsedByMyself(ApiTester $I)
    {
        $I->wantTo('succeed if the email address is used by myself.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $updateStaff = $this->examples->staffs[2];
        $param = ['officeGroupIds' => [1], 'email' => $updateStaff->email] + $this->buildParam($updateStaff);

        $I->sendPUT("staffs/{$updateStaff->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
    }

    /**
     * 指定した E-mail アドレスを使用しているのが退職済みのスタッフの時は正常終了するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedIfEmailAddressIsUsedByRetiredStaff(ApiTester $I)
    {
        $I->wantTo('succeed if the email address is used by retired staff.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $updateStaff = $this->examples->staffs[2];
        $retiredStaff = $this->examples->staffs[34];
        $param = ['officeGroupIds' => [1], 'email' => $retiredStaff->email] + $this->buildParam($updateStaff);

        $I->sendPUT("staffs/{$updateStaff->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
    }

    /**
     * 有効なスタッフに使用されている E-mail アドレスを指定すると400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestIfEmailAddressIsUsedByValidStaff(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest if the email address is used by valid staff.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $updateStaff = $this->examples->staffs[2];
        $param = ['officeGroupIds' => [1], 'email' => $staff->email] + $this->buildParam($updateStaff);

        $I->sendPUT("staffs/{$updateStaff->id}", $param);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['email' => ['このメールアドレスはすでに使用されています。']]]);
    }

    /**
     * 存在しないIDを指定したら404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when Invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $updateStaff = $this->examples->staffs[2];
        $param = $this->buildParam($updateStaff);

        $I->sendPUT("staffs/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
    }

    /**
     * 他の事業者のIDを指定すると404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is other Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $updateStaff = $this->examples->staffs[1];
        $param = $this->buildParam($updateStaff);
        $id = $updateStaff->id;

        $I->sendPUT("staffs/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
    }

    /**
     * IDが許可された事業所に存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->staffs[4]->id;

        $I->sendPUT("staffs/{$id}", $this->buildParam($this->examples->staffs[2]));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
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

        $I->sendPUT("staffs/{$staff->id}", $this->buildParam($this->examples->staffs[2]));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * パラメータの組み立て.
     *
     * @param Staff $domain
     * @throws \JsonException
     * @return array
     */
    private function buildParam(Staff $domain): array
    {
        $staff = $this->domainToArray($domain);
        return ['email' => 'not-conflict@example.com'] + $staff['name'] + $staff['addr'] + $staff + ['password' => 'PassWoRD'];
    }
}
