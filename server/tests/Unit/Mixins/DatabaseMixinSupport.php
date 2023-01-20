<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Laravel\Lumen\Application;
use function PHPUnit\Framework\assertEquals;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\AttendanceFixture;
use Tests\Unit\Fixtures\BankAccountFixture;
use Tests\Unit\Fixtures\CallingFixture;
use Tests\Unit\Fixtures\CallingLogFixture;
use Tests\Unit\Fixtures\CallingResponseFixture;
use Tests\Unit\Fixtures\ContractFixture;
use Tests\Unit\Fixtures\DwsAreaGradeFeeFixture;
use Tests\Unit\Fixtures\DwsAreaGradeFixture;
use Tests\Unit\Fixtures\DwsBillingBundleFixture;
use Tests\Unit\Fixtures\DwsBillingCopayCoordinationFixture;
use Tests\Unit\Fixtures\DwsBillingFixture;
use Tests\Unit\Fixtures\DwsBillingInvoiceFixture;
use Tests\Unit\Fixtures\DwsBillingServiceReportFixture;
use Tests\Unit\Fixtures\DwsBillingStatementFixture;
use Tests\Unit\Fixtures\DwsCertificationFixture;
use Tests\Unit\Fixtures\DwsHomeHelpServiceChunkFixture;
use Tests\Unit\Fixtures\DwsHomeHelpServiceDictionaryEntryFixture;
use Tests\Unit\Fixtures\DwsHomeHelpServiceDictionaryFixture;
use Tests\Unit\Fixtures\DwsProjectFixture;
use Tests\Unit\Fixtures\DwsProjectServiceMenuFixture;
use Tests\Unit\Fixtures\DwsProvisionReportFixture;
use Tests\Unit\Fixtures\DwsVisitingCareForPwsdDictionaryEntryFixture;
use Tests\Unit\Fixtures\DwsVisitingCareForPwsdDictionaryFixture;
use Tests\Unit\Fixtures\HomeHelpServiceCalcSpecFixture;
use Tests\Unit\Fixtures\HomeVisitLongTermCareCalcSpecFixture;
use Tests\Unit\Fixtures\InvitationFixture;
use Tests\Unit\Fixtures\JobFixture;
use Tests\Unit\Fixtures\LtcsAreaGradeFeeFixture;
use Tests\Unit\Fixtures\LtcsAreaGradeFixture;
use Tests\Unit\Fixtures\LtcsBillingBundleFixture;
use Tests\Unit\Fixtures\LtcsBillingFixture;
use Tests\Unit\Fixtures\LtcsBillingInvoiceFixture;
use Tests\Unit\Fixtures\LtcsBillingStatementFixture;
use Tests\Unit\Fixtures\LtcsHomeVisitLongTermCareDictionaryEntryFixture;
use Tests\Unit\Fixtures\LtcsHomeVisitLongTermCareDictionaryFixture;
use Tests\Unit\Fixtures\LtcsInsCardFixture;
use Tests\Unit\Fixtures\LtcsProjectFixture;
use Tests\Unit\Fixtures\LtcsProjectServiceMenuFixture;
use Tests\Unit\Fixtures\LtcsProvisionReportFixture;
use Tests\Unit\Fixtures\OfficeFixture;
use Tests\Unit\Fixtures\OfficeGroupFixture;
use Tests\Unit\Fixtures\OrganizationFixture;
use Tests\Unit\Fixtures\OrganizationSettingFixture;
use Tests\Unit\Fixtures\OwnExpenseProgramFixture;
use Tests\Unit\Fixtures\PermissionGroupFixture;
use Tests\Unit\Fixtures\RoleFixture;
use Tests\Unit\Fixtures\ShiftFixture;
use Tests\Unit\Fixtures\StaffEmailVerificationFixture;
use Tests\Unit\Fixtures\StaffFixture;
use Tests\Unit\Fixtures\StaffPasswordResetFixture;
use Tests\Unit\Fixtures\StaffRememberTokenFixture;
use Tests\Unit\Fixtures\UserBillingFixture;
use Tests\Unit\Fixtures\UserDwsCalcSpecFixture;
use Tests\Unit\Fixtures\UserDwsSubsidyFixture;
use Tests\Unit\Fixtures\UserFixture;
use Tests\Unit\Fixtures\UserLtcsCalcSpecFixture;
use Tests\Unit\Fixtures\UserLtcsSubsidyFixture;
use Tests\Unit\Fixtures\VisitingCareForPwsdCalcSpecFixture;
use Tests\Unit\Fixtures\WithdrawalTransactionFixture;

/**
 * {@link \Tests\Unit\Mixins\DatabaseMixin} 用ユーティリティ.
 */
final class DatabaseMixinSupport
{
    use AttendanceFixture;
    use BankAccountFixture;
    use CallingFixture;
    use CallingLogFixture;
    use CallingResponseFixture;
    use ContractFixture;
    use DwsAreaGradeFeeFixture;
    use DwsAreaGradeFixture;
    use DwsBillingBundleFixture;
    use DwsBillingCopayCoordinationFixture;
    use DwsBillingFixture;
    use DwsBillingInvoiceFixture;
    use DwsBillingServiceReportFixture;
    use DwsBillingStatementFixture;
    use DwsCertificationFixture;
    use DwsHomeHelpServiceChunkFixture;
    use DwsHomeHelpServiceDictionaryEntryFixture;
    use DwsHomeHelpServiceDictionaryFixture;
    use DwsProvisionReportFixture;
    use DwsVisitingCareForPwsdDictionaryEntryFixture;
    use DwsVisitingCareForPwsdDictionaryFixture;
    use ExamplesConsumer;
    use HomeHelpServiceCalcSpecFixture;
    use HomeVisitLongTermCareCalcSpecFixture;
    use InvitationFixture;
    use JobFixture;
    use LtcsAreaGradeFeeFixture;
    use LtcsAreaGradeFixture;
    use LtcsBillingBundleFixture;
    use LtcsBillingFixture;
    use LtcsBillingInvoiceFixture;
    use LtcsBillingStatementFixture;
    use LtcsHomeVisitLongTermCareDictionaryEntryFixture;
    use LtcsHomeVisitLongTermCareDictionaryFixture;
    use LtcsInsCardFixture;
    use LtcsProvisionReportFixture;
    use OfficeFixture;
    use OfficeGroupFixture;
    use OrganizationFixture;
    use OrganizationSettingFixture;
    use OwnExpenseProgramFixture;
    use PermissionGroupFixture;
    use DwsProjectFixture;
    use LtcsProjectFixture;
    use RoleFixture;
    use DwsProjectServiceMenuFixture;
    use LtcsProjectServiceMenuFixture;
    use ShiftFixture;
    use StaffEmailVerificationFixture;
    use StaffFixture;
    use StaffPasswordResetFixture;
    use StaffRememberTokenFixture;
    use UserDwsSubsidyFixture;
    use UserFixture;
    use UserBillingFixture;
    use UserDwsCalcSpecFixture;
    use UserLtcsCalcSpecFixture;
    use UserLtcsSubsidyFixture;
    use VisitingCareForPwsdCalcSpecFixture;
    use WithdrawalTransactionFixture;

    private static bool $fixed = false;

    private static bool $migrated = false;

    /**
     * マイグレーションを実行する.
     *
     * @param \Laravel\Lumen\Application $app
     * @return void
     */
    public static function migrate(Application $app): void
    {
        $env = $app->environment();
        $command = 'php ' . $app->basePath('../artisan') . ' -q --env=' . $env . ' migrate:fresh';
        $message = system($command, $exitStatus);
        assertEquals(0, $exitStatus, $message);
        self::$migrated = true;
    }

    /**
     * マイグレーションを一度だけ実行する.
     *
     * @param \Laravel\Lumen\Application $app
     * @return void
     */
    public static function migrateOnce(Application $app): void
    {
        if (self::$migrated === false) {
            self::migrate($app);
        }
    }

    /**
     * テストデータ（フィクスチャ）を登録する.
     *
     * @return void
     */
    public static function fixture(): void
    {
        $instance = new self();

        $instance->createDwsAreaGrades();
        $instance->createLtcsAreaGrades();

        $instance->createOrganizations();

        $instance->createOfficeGroups();
        $instance->createOffices();

        $instance->createBankAccounts();

        $instance->createPermissionGroups();
        $instance->createRoles();
        $instance->createStaffs();
        $instance->createStaffPasswordResets();
        $instance->createStaffRememberTokens();
        $instance->createStaffEmailVerifications();

        $instance->createUsers();
        $instance->createDwsCertifications();
        $instance->createLtcsInsCards();
        $instance->createContracts();

        $instance->createShifts();

        $instance->createJobs();

        $instance->createAttendances();

        $instance->createUserDwsSubsidies();
        $instance->createUserLtcsSubsidies();
        $instance->createUserDwsCalcSpecs();
        $instance->createUserLtcsCalcSpecs();

        $instance->createHomeHelpServiceCalcSpecs();

        $instance->createVisitingCareForPwsdCalcSpecs();

        $instance->createHomeVisitLongTermCareCalcSpecs();

        $instance->createDwsProjectServiceMenus();
        $instance->createLtcsProjectServiceMenus();

        $instance->createCallings();
        $instance->createCallingLogs();
        $instance->createCallingResponses();

        $instance->createDwsHomeHelpServiceDictionaries();
        $instance->createDwsHomeHelpServiceDictionaryEntries();
        $instance->createDwsVisitingCareForPwsdDictionaries();
        $instance->createDwsVisitingCareForPwsdDictionaryEntries();
        $instance->createLtcsHomeVisitLongTermCareDictionaries();
        $instance->createLtcsHomeVisitLongTermCareDictionaryEntries();

        $instance->createDwsBillings();
        $instance->createDwsBillingBundles();
        $instance->createDwsBillingInvoices();
        $instance->createDwsBillingServiceReports();
        $instance->createDwsBillingStatements();
        $instance->createDwsBillingCopayCoordinations();
        $instance->createLtcsBillings();
        $instance->createLtcsBillingBundles();
        $instance->createLtcsBillingInvoices();
        $instance->createLtcsBillingStatements();

        $instance->createDwsAreaGradeFeeFixture();
        $instance->createLtcsAreaGradeFeeFixture();

        $instance->createOwnExpensePrograms();

        $instance->createDwsProjects();
        $instance->createLtcsProjects();

        $instance->createLtcsProvisionReports();
        $instance->createDwsProvisionReports();

        $instance->createInvitations();

        $instance->createOrganizationSettings();

        $instance->createUserBillings();
        $instance->createWithdrawalTransactions();

        // temporary向けFixture
        // TODO DEV-3849 ここで実施するとAPIテストの最初のテストケースでうまく行かなくなるため、蓋をしている
//        $instance->createDwsHomeHelpServiceChunk();

        self::$fixed = true;
    }

    /**
     * テストデータ（フィクスチャ）を一度だけ登録する.
     *
     * @return void
     */
    public static function fixtureOnce(): void
    {
        if (self::$fixed === false) {
            self::fixture();
        }
    }
}
