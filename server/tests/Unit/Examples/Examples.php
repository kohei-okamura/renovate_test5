<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Lib\LazyField;

/**
 * テストデータ生成器の基底クラス.
 */
final class Examples
{
    use BankAccountExample;
    use CallingExample;
    use CallingLogExample;
    use CallingResponseExample;
    use ContractExample;
    use DwsAreaGradeExample;
    use DwsAreaGradeFeeExample;
    use DwsBillingExample;
    use DwsBillingBundleExample;
    use DwsBillingCopayCoordinationExample;
    use DwsBillingInvoiceExample;
    use DwsBillingServiceDetailExample;
    use DwsBillingServiceReportExample;
    use DwsBillingStatementExample;
    use DwsCertificationExample;
    use DwsHomeHelpServiceChunkExample;
    use DwsHomeHelpServiceDictionaryExample;
    use DwsHomeHelpServiceDictionaryEntryExample;
    use DwsHomeHelpServiceDurationExample;
    use DwsProjectExample;
    use DwsProjectServiceMenuExample;
    use DwsProvisionReportExample;
    use DwsVisitingCareForPwsdChunkExample;
    use DwsVisitingCareForPwsdDictionaryExample;
    use DwsVisitingCareForPwsdDictionaryEntryExample;
    use HomeHelpServiceCalcSpecExample;
    use HomeVisitLongTermCareCalcSpecExample;
    use InvitationExample;
    use JobExample;
    use LazyField;
    use LtcsAreaGradeExample;
    use LtcsAreaGradeFeeExample;
    use LtcsBillingBundleExample;
    use LtcsBillingExample;
    use LtcsBillingInvoiceExample;
    use LtcsBillingServiceDetailExample;
    use LtcsBillingStatementExample;
    use LtcsHomeVisitLongTermCareDictionaryEntryExample;
    use LtcsHomeVisitLongTermCareDictionaryExample;
    use LtcsProjectExample;
    use LtcsProjectServiceMenuExample;
    use LtcsInsCardExample;
    use LtcsProvisionReportExample;
    use LtcsProvisionReportSheetAppendixEntryExample;
    use LtcsProvisionReportSheetAppendixExample;
    use OfficeExample;
    use OfficeGroupExample;
    use OrganizationExample;
    use OrganizationSettingExample;
    use OwnExpenseProgramExample;
    use PermissionGroupExample;
    use ShiftExample;
    use AttendanceExample;
    use RoleExample;
    use StaffEmailVerificationExample;
    use StaffExample;
    use StaffPasswordResetExample;
    use StaffRememberTokenExample;
    use UserBillingExample;
    use UserDwsCalcSpecExample;
    use UserLtcsCalcSpecExample;
    use UserDwsSubsidyExample;
    use UserLtcsSubsidyExample;
    use UserExample;
    use ShiftExample;
    use VisitingCareForPwsdCalcSpecExample;
    use WithdrawalTransactionExample;

    /**
     * @var static
     */
    private static $instance;

    /**
     * Examples constructor.
     */
    private function __construct()
    {
        // Nothing to do.
    }

    /**
     * インスタンスを生成する.
     *
     * @return static
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
