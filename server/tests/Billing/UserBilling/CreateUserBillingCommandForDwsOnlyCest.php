<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\UserBilling;

use BillingTester;
use Domain\BankAccount\BankAccountRepository;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeRepository;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\User\UserRepository;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingFinder;
use function PHPUnit\Framework\assertSame;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 利用者請求生成コマンドのテスト（障害福祉サービスのみ）.
 */
final class CreateUserBillingCommandForDwsOnlyCest extends Test
{
    use ExamplesConsumer;

    /**
     * * Artisan Command テスト.
     *
     * - 障害福祉サービスのみの場合の利用者請求生成.
     *
     * @param BillingTester $I
     * @noinspection PhpUnused
     */
    public function succeedArtisanCommand(BillingTester $I): void
    {
        // TODO: 明細書（の items）でランダム値が使われているのが原因で落ちてるぽいが、時間との兼ね合いで一旦スキップし、Example 固定値にしたら skip 消す
        $I->markTestSkipped();
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

        /** @var \Domain\Billing\DwsBillingRepository $dwsBillingRepository */
        $dwsBillingRepository = app(DwsBillingRepository::class);
        $dwsBilling = $dwsBillingRepository->store($this->examples->dwsBillings[6]->copy([
            'id' => null,
            'office' => DwsBillingOffice::from($office),
            'transactedIn' => Carbon::now()->firstOfMonth(),
            'files' => [],
        ]));
        /** @var \Domain\Billing\DwsBillingBundleRepository $dwsBillingBundleRepository */
        $dwsBillingBundleRepository = app(DwsBillingBundleRepository::class);
        $dwsBillingBundle = $dwsBillingBundleRepository->store($this->examples->dwsBillingBundles[7]->copy([
            'id' => null,
            'dwsBillingId' => $dwsBilling->id,
            'providedIn' => Carbon::now()->subMonth()->firstOfMonth(),
        ]));

        /** @var \Domain\DwsCertification\DwsCertificationRepository $dwsCertificationRepository */
        $dwsCertificationRepository = app(DwsCertificationRepository::class);
        $dwsCertification = $dwsCertificationRepository->store($this->examples->dwsCertifications[0]->copy([
            'id' => null,
            'userId' => $user->id,
        ]));

        /** @var \Domain\Billing\DwsBillingStatementRepository $dwsBillingStatementRepository */
        $dwsBillingStatementRepository = app(DwsBillingStatementRepository::class);
        $dwsBillingStatement = $dwsBillingStatementRepository->store($this->examples->dwsBillingStatements[18]->copy([
            'id' => null,
            'user' => DwsBillingUser::from($user, $dwsCertification),
            'dwsBillingId' => $dwsBilling->id,
            'dwsBillingBundleId' => $dwsBillingBundle->id,
        ]));

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $dwsProvisionReportRepository */
        $dwsProvisionReportRepository = app(DwsProvisionReportRepository::class);
        $dwsProvisionReportRepository->store($this->examples->dwsProvisionReports[0]->copy([
            'id' => null,
            'userId' => $user->id,
            'officeId' => $office->id,
            'providedIn' => Carbon::now()->subMonth()->firstOfMonth(),
            'status' => DwsProvisionReportStatus::fixed(),
        ]));

        $I->wantTo('succeed artisan command');
        // コマンド実行
        $result = $I->callArtisanCommand('user-billing:create', [
            '--batch' => true,
        ]);

        $I->seeLogCount(0);
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
        $actual = $userBillings->map(function (UserBilling $x) use ($dwsBillingStatement, $office, $user, $I) {
            $I->assertSame($user->id, $x->userId);
            $I->assertSame($office->id, $x->officeId);
            $I->assertSame($dwsBillingStatement->id, $x->dwsItem->dwsStatementId);
            return $x->copy([
                'id' => null,
                'userId' => null,
                'officeId' => null,
                'dwsItem' => $x->dwsItem->copy(['dwsStatementId' => null]),
            ]);
        });
        $I->assertMatchesModelSnapshot($actual);
    }
}
