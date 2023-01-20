<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Calling;

use ApiTester;
use Domain\Calling\Calling;
use Domain\Calling\CallingLogFinder;
use Domain\Calling\CallingRepository;
use Domain\Calling\CallingType;
use Domain\Common\Carbon;
use Domain\Staff\Staff;
use Domain\Staff\StaffRepository;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * SendThirdCallingCommand のテスト.
 */
class SendThirdCallingCommandCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * Artisan Command テスト.
     *
     * @param ApiTester $I
     */
    public function succeedArtisanCommand(ApiTester $I)
    {
        $I->markTestSkipped('外部APIを呼んでいてローカルで落ちるので一旦スキップ');
        $I->wantTo('succeed artisan command');

        // calling を作成
        $calling = Calling::create([
            'staffId' => $this->examples->shifts[0]->assignees[0]->staffId, //  = 11
            'shiftIds' => [$this->examples->shifts[0]->id],
            'token' => 'dn1H2mSQEnaYwXdsozJOmnXuvmgqJeYhNDU60cMNpVD8ExOjPFkuypPA65Oa',
            'expiredAt' => Carbon::now()->addMinutes(70),
            'createdAt' => Carbon::now(),
        ]);
        /** @var \Domain\Calling\CallingRepository $callingRepository */
        $callingRepository = app(CallingRepository::class);
        $calling = $callingRepository->store($calling);

        // コマンド実行
        $result = $I->callArtisanCommand('calling:third-notify', [
            '--batch' => true,
        ]);

        $I->assertSame(self::COMMAND_SUCCESS, $result);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '出勤確認送信履歴が登録されました', [
            'id' => '*',
            'organizationId' => $this->examples->shifts[0]->organizationId,
            'staffId' => '',
        ]);

        // callingLog の確認
        $callingLogFinder = app(CallingLogFinder::class);
        /** @var \Domain\Calling\CallingLog[]|\Domain\FinderResult $callingLogs */
        $callingLogs = $callingLogFinder->find(['callingId' => $calling->id], ['sortBy' => 'id']);
        /** @var \Domain\Calling\CallingLog $callingLog */
        $callingLog = $callingLogs->list->head();
        $I->assertEquals(CallingType::telephoneCall(), $callingLog->callingType);
        $I->assertTrue($callingLog->isSucceeded);

        // Twilio にちゃんと送られているかのチェックはできないようなので、
        // 以上でテスト完了とする.
        // NOTE: https://jp.twilio.com/docs/iam/test-credentials
    }

    /**
     * 無効な電話番号の場合.
     *
     * @param ApiTester $I
     */
    public function failWithInvalidNumber(ApiTester $I)
    {
        $I->skipTest(); // DEV-6195 不正な電話番号の場合のテストを行う.
        $I->wantTo('fail with invalid number.');

        $staff = $this->examples->staffs[0]->copy([
            'tel' => '+15005550001',
            'version' => $this->examples->staffs[0]->version + 1,
        ]);
        $this->updateStaff($staff);

        // calling を作成
        $calling = Calling::create([
            'staffId' => $staff->id,
            'shiftIds' => [$this->examples->shifts[0]->id],
            'token' => 'dn1H2mSQEnaYwXdsozJOmnXuvmgqJeYhNDU60cMNpVD8ExOjPFkuypPA65Oa',
            'expiredAt' => Carbon::now()->addMinutes(70),
            'createdAt' => Carbon::now(),
        ]);
        /** @var \Domain\Calling\CallingRepository $callingRepository */
        $callingRepository = app(CallingRepository::class);
        $calling = $callingRepository->store($calling);

        // コマンド実行
        $result = $I->callArtisanCommand('calling:third-notify', [
            '--batch' => true,
        ]);
    }

    /**
     * StaffRepository を取得する.
     *
     * @return \Domain\Staff\StaffRepository
     */
    private function getStaffRepository(): StaffRepository
    {
        return app(StaffRepository::class);
    }

    /**
     * スタッフを更新する.
     *
     * @param \Domain\Staff\Staff $staff
     * @return \Domain\Staff\Staff
     */
    private function updateStaff(Staff $staff): Staff
    {
        return $this->getStaffRepository()
            ->store($staff);
    }
}
