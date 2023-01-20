<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Invitation;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Staff\Invitation;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Invitation create のテスト.
 *
 * POST /invitations
 */
class CreateInvitationCest extends InvitationTest
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
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call');

        // maildev 内のメールを削除する.
        $I->clearReceivedMail();

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $invitation = Invitation::create([
            'email' => $this->examples->invitations[0]->email,
            'officeIds' => $this->examples->invitations[0]->officeIds,
            'roleIds' => $this->examples->invitations[0]->roleIds,
            'officeGroupIds' => $this->examples->invitations[0]->officeGroupIds,
        ]);

        $I->sendPOST('invitations', $this->domainToArray($invitation));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '招待が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // mail の確認
        $I->seeReceivedMailCount(1);
        $mailId = $I->grabMailList()[0]['id'];
        $I->seeReceivedMail($mailId, [
            'subject' => 'careid アカウントへ招待されました',
            'headers.to' => $invitation->email,
        ]);
    }

    /**
     * 無効なスタッフに使用されている E-mail アドレスを指定しても正常に処理が行われるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfEmailAddressIsUsedByInvalidStaff(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call if the email address is used by invalid staff.');

        // maildev 内のメールを削除する.
        $I->clearReceivedMail();

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $invitation = Invitation::create([
            'email' => $this->examples->staffs[16]->email,
            'officeIds' => $this->examples->invitations[0]->officeIds,
            'roleIds' => $this->examples->invitations[0]->roleIds,
            'officeGroupIds' => $this->examples->invitations[0]->officeGroupIds,
        ]);

        $I->sendPOST('invitations', $this->domainToArray($invitation));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '招待が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // mail の確認
        $I->seeReceivedMailCount(1);
        $mailId = $I->grabMailList()[0]['id'];
        $I->seeReceivedMail($mailId, [
            'subject' => 'careid アカウントへ招待されました',
            'headers.to' => $invitation->email,
        ]);
    }

    /**
     * 退職済みのスタッフに使用されている E-mail アドレスを指定しても正常に処理が行われるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfEmailAddressIsUsedByRetiredStaff(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call if the email address is used by retired staff.');

        // maildev 内のメールを削除する.
        $I->clearReceivedMail();

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $invitation = Invitation::create([
            'email' => $this->examples->staffs[34]->email,
            'officeIds' => $this->examples->invitations[0]->officeIds,
            'roleIds' => $this->examples->invitations[0]->roleIds,
            'officeGroupIds' => $this->examples->invitations[0]->officeGroupIds,
        ]);

        $I->sendPOST('invitations', $this->domainToArray($invitation));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '招待が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // mail の確認
        $I->seeReceivedMailCount(1);
        $mailId = $I->grabMailList()[0]['id'];
        $I->seeReceivedMail($mailId, [
            'subject' => 'careid アカウントへ招待されました',
            'headers.to' => $invitation->email,
        ]);
    }

    /**
     * 有効なスタッフに使用されている E-mail アドレスを指定すると400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestIfEmailAddressIsUsedByValidStaff(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('fail with BadRequest if the email address is used by valid staff.');

        // maildev 内のメールを削除する.
        $I->clearReceivedMail();

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $invitation = Invitation::create([
            'email' => $staff->email,
            'officeIds' => $this->examples->invitations[0]->officeIds,
            'roleIds' => $this->examples->invitations[0]->roleIds,
            'officeGroupIds' => $this->examples->invitations[0]->officeGroupIds,
        ]);

        $I->sendPOST('invitations', $this->domainToArray($invitation));

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['email' => ['このメールアドレスはすでに使用されています。']]]);
    }
}
