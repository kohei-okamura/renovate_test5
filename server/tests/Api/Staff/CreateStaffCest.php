<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staffs create のテスト.
 *
 * POST /staffs
 */
class CreateStaffCest extends StaffTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $param = $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '招待が更新されました', [
            'id' => '*',
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'スタッフが登録されました', [
            'id' => '*',
            'organizationId' => $this->examples->organizations[0]->id, // ホスト名から決め打ちされている
        ]);
    }

    /**
     * 招待のメールアドレスを使用していたスタッフが退職済みの場合は正常終了するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedWhenStaffUsingEmailOfInvitationIsAlreadyRetired(ApiTester $I)
    {
        $I->wantTo('succeed when staff using email of invitation is already retired');

        $invitations = $this->examples->invitations[6];
        $param = [
            'invitationId' => $invitations->id,
            'token' => $invitations->token,
        ] + $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '招待が更新されました', [
            'id' => '*',
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'スタッフが登録されました', [
            'id' => '*',
            'organizationId' => $this->examples->organizations[0]->id, // ホスト名から決め打ちされている
        ]);
    }

    /**
     * 招待IDが存在しない場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenInvitationIdDoesNotExistInDB(ApiTester $I)
    {
        $I->wantTo('fail with bad request when invitationId does not exist in DB');

        $param = ['invitationId' => self::NOT_EXISTING_ID] + $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['invitationId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 招待Entityとトークンが一致しない場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenTokenDoesNotMatch(ApiTester $I)
    {
        $I->wantTo('fail with bad request when token does not match');

        $param = ['token' => self::NOT_EXISTING_TOKEN] + $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['token' => ['無効なトークンです。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 招待のメールアドレスが登録済みの場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenEmailOfInvitationIsAlreadyUsed(ApiTester $I)
    {
        $I->wantTo('fail with bad request when email of invitation is already used');

        $param = [
            'invitationId' => $this->examples->invitations[5]->id,
            'token' => $this->examples->invitations[5]->token,
        ] + $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['invitationId' => ['このメールアドレスはすでに使用されているため、登録できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * トークンの有効期限が切れている場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenTokenHasBeenExpired(ApiTester $I)
    {
        $I->wantTo('fail with bad request when token has been expired');

        $param = [
            'invitationId' => $this->examples->invitations[4]->id,
            'token' => $this->examples->invitations[4]->token,
        ] + $this->defaultParam();

        $I->sendPOST('staffs', $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Invitation({$this->examples->invitations[4]->id}) not found");
    }

    /**
     * テストパラメータの設定.
     *
     * @throws \JsonException
     * @return array
     */
    private function defaultParam(): array
    {
        $staff = $this->domainToArray($this->examples->staffs[0]->copy([
            'email' => null,
        ]));
        return $staff['name'] + $staff['addr'] + $staff + ['password' => 'PassWoRD']
            + [
                'invitationId' => $this->examples->invitations[0]->id,
                'token' => $this->examples->invitations[0]->token,
            ];
    }
}
