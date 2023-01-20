<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * SendFirstCallingCommand のテスト.
 */
class SendFirstCallingCommandCest extends Test
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

        // maildev 内のメールを削除する.
        $I->clearReceivedMail();

        // calling を作成
        $calling = Calling::create([
            'staffId' => $this->examples->shifts[0]->assignees[0]->staffId, //  = 11
            'shiftIds' => [$this->examples->shifts[0]->id],
            'token' => 'dn1H2mSQEnaYwXdsozJOmnXuvmgqJeYhNDU60cMNpVD8ExOjPFkuypPA65Oa',
            'expiredAt' => Carbon::now()->addMinutes(120),
            'createdAt' => Carbon::now(),
        ]);
        /** @var \Domain\Calling\CallingRepository $callingRepository */
        $callingRepository = app(CallingRepository::class);
        $calling = $callingRepository->store($calling);

        // コマンド実行
        $result = $I->callArtisanCommand('calling:first-notify', [
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
        $callingFinder = app(CallingLogFinder::class);
        /** @var \Domain\Calling\CallingLog[]|\Domain\FinderResult $callingLogs */
        $callingLogs = $callingFinder->find(['callingId' => $calling->id], ['sortBy' => 'id']);
        /** @var \Domain\Calling\CallingLog $callingLog */
        $callingLog = $callingLogs->list->head();
        $I->assertEquals(CallingType::mail(), $callingLog->callingType);
        $I->assertTrue($callingLog->isSucceeded);

        // mail の確認
        $I->seeReceivedMailCount(1);
        $mailId = $I->grabMailList()[0]['id'];
        $I->seeReceivedMail($mailId, [
            'subject' => '本日のシフトが2時間後に開始されます',
            'headers.to' => $this->examples->staffs[10]->email,
        ]);
    }
}
