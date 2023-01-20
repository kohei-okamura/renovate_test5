<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\UserBilling;

use BillingTester;
use Domain\BankAccount\BankAccountRepository;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingRepository;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsBillingUser;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeRepository;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\User\UserRepository;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingFinder;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 利用者請求生成コマンドのテスト（介護保険サービスのみ）.
 */
final class CreateUserBillingCommandForLtcsOnlyCest extends Test
{
    use ExamplesConsumer;

    /**
     * Artisan Command テスト.
     *
     * - 介護福祉サービスのみの場合の利用者請求生成.
     *
     * @param BillingTester $I
     * @noinspection PhpUnused
     */
    public function succeedLtcsBillingItemOnly(BillingTester $I)
    {
        Carbon::setTestNow('2021-09-01 00:00:00');
        /** @var \Domain\BankAccount\BankAccountRepository $bankAccountRepository */
        $bankAccountRepository = app(BankAccountRepository::class);
        $bankAccount = $bankAccountRepository->store($this->examples->bankAccounts[20]->copy([
            'id' => null,
        ]));

        /** @var \Domain\User\UserRepository $userRepository */
        $userRepository = app(UserRepository::class);
        $user = $userRepository->store($this->examples->users[0]->copy([
            'id' => null,
            'name' => new StructuredName(
                familyName: '利用者請求',
                givenName: '生成テスト',
                phoneticFamilyName: 'リヨウシャセイキュウ',
                phoneticGivenName: 'セイセイテスト',
            ),
            'addr' => new Addr(
                postcode: '1640000',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: 'ここどこ町',
                apartment: '架空建物',
            ),
            'bankAccountId' => $bankAccount->id,
        ]));

        /** @var \Domain\Office\OfficeRepository $officeRepository */
        $officeRepository = app(OfficeRepository::class);
        $office = $officeRepository->store($this->examples->offices[24]->copy([
            'id' => null,
            'dwsGenericService' => OfficeDwsGenericService::create([
                'dwsAreaGradeId' => $this->examples->dwsAreaGrades[5]->id,
                'code' => '1234567890',
                'openedOn' => Carbon::now()->startOfDay(),
                'designationExpiredOn' => Carbon::now()->startOfDay(),
            ]),
        ]));

        /** @var \Domain\Billing\LtcsBillingRepository $ltcsBillingRepository */
        $ltcsBillingRepository = app(LtcsBillingRepository::class);
        $ltcsBilling = $ltcsBillingRepository->store($this->examples->ltcsBillings[0]->copy([
            'id' => null,
            'office' => LtcsBillingOffice::from($office),
            'transactedIn' => Carbon::now()->firstOfMonth(),
            'status' => LtcsBillingStatus::fixed(),
            'files' => [],
        ]));
        /** @var \Domain\Billing\LtcsBillingBundleRepository $ltcsBillingBundleRepository */
        $ltcsBillingBundleRepository = app(LtcsBillingBundleRepository::class);
        $ltcsBillingBundle = $ltcsBillingBundleRepository->store($this->examples->ltcsBillingBundles[7]->copy([
            'id' => null,
            'billingId' => $ltcsBilling->id,
            'providedIn' => Carbon::now()->subMonth()->firstOfMonth(),
        ]));

        /** @var \Domain\LtcsInsCard\LtcsInsCardRepository $ltcsInsCardRepository */
        $ltcsInsCardRepository = app(LtcsInsCardRepository::class);
        $ltcsInsCard = $ltcsInsCardRepository->store($this->examples->ltcsInsCards[0]->copy([
            'id' => null,
            'userId' => $user->id,
        ]));

        /** @var \Domain\Billing\LtcsBillingStatementRepository $ltcsBillingStatementRepository */
        $ltcsBillingStatementRepository = app(LtcsBillingStatementRepository::class);
        $ltcsBillingStatement = $ltcsBillingStatementRepository->store($this->examples->ltcsBillingStatements[9]->copy([
            'id' => null,
            'user' => LtcsBillingUser::from($user, $ltcsInsCard),
            'billingId' => $ltcsBilling->id,
            'bundleId' => $ltcsBillingBundle->id,
        ]));

        /** @var \Domain\ProvisionReport\LtcsProvisionReportRepository $ltcsProvisionReportRepository */
        $ltcsProvisionReportRepository = app(LtcsProvisionReportRepository::class);
        $ltcsProvisionReport = $ltcsProvisionReportRepository->store($this->examples->ltcsProvisionReports[0]->copy([
            'id' => null,
            'userId' => $user->id,
            'officeId' => $office->id,
            'providedIn' => Carbon::now()->subMonth()->firstOfMonth(),
            'status' => LtcsProvisionReportStatus::fixed(),
        ]));

        $I->wantTo('succeed artisan command');
        // コマンド実行
        $result = $I->callArtisanCommand('user-billing:create', [
            '--batch' => true,
        ]);

        $I->seeLogCount(6);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求生成開始', [
            'userId' => $user->id,
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求生成：障害福祉サービス予実', [
            'id' => '',
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者請求生成：介護保険サービス予実', [
            'id' => $ltcsProvisionReport->id,
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, '利用者請求生成：障害福祉サービス明細書', [
            'id' => '',
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        $I->seeLogMessage(4, LogLevel::INFO, '利用者請求生成：介護保険サービス明細書', [
            'id' => $ltcsBillingStatement->id,
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        $I->seeLogMessage(5, LogLevel::INFO, '利用者請求生成終了', [
            'userId' => $user->id,
            'organizationId' => $this->examples->organizations[0]->id,
            'staffId' => '',
        ]);
        assertSame(self::COMMAND_SUCCESS, $result);

        /** @var \Domain\UserBilling\UserBillingFinder $userBillingFinder */
        $userBillingFinder = app(UserBillingFinder::class);
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        $userBillings = $userBillingFinder
            ->find(['providedIn' => Carbon::now()->subMonth()->firstOfMonth()], $paginationParams)
            ->list;
        // 以下通しじゃないとidが異なってしまう。
        // 単体実行時と全体実行時で差がでないようにスナップショットでは各種 id をnullにしておく
        $actual = $userBillings->map(function (UserBilling $x) use ($ltcsBillingStatement, $office, $user, $I) {
            $I->assertSame($user->id, $x->userId);
            $I->assertSame($office->id, $x->officeId);
            $I->assertSame($ltcsBillingStatement->id, $x->ltcsItem->ltcsStatementId);
            return $x->copy([
                'id' => null,
                'userId' => null,
                'officeId' => null,
                'ltcsItem' => $x->ltcsItem->copy(['ltcsStatementId' => null]),
            ]);
        });
        $I->assertMatchesModelSnapshot($actual);
    }
}
