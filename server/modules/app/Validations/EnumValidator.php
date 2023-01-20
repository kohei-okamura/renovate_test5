<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\BankAccount\BankAccountType;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsExpiredReason;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\ContactRelationship;
use Domain\Common\DayOfWeek;
use Domain\Common\DefrayerCategory;
use Domain\Common\Prefecture;
use Domain\Common\Recurrence;
use Domain\Common\Rounding;
use Domain\Common\ServiceSegment;
use Domain\Common\Sex;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\Contract\ContractStatus;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Business;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\Role\RoleScope;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\Activity;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Domain\Staff\Certification;
use Domain\Staff\StaffStatus;
use Domain\User\BillingDestination;
use Domain\User\DwsUserLocationAddition;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\PaymentMethod;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBillingUsedService\UserBillingUsedService;
use ScalikePHP\Map;

/**
 * Enum存在チェック用カスタムバリデータ
 *
 * CustomValidatorからのみuseする
 */
trait EnumValidator
{
    /**
     * 入力値が列挙型「勤務内容」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateActivity(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Activity::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「銀行口座種別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateBankAccountType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && BankAccountType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「請求先」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateBillingDestination(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value)
            && BillingDestination::isValid((int)$value)
            && BillingDestination::none()->value() !== $value;
    }

    /**
     * 入力値が列挙型「事業内容」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateBusiness(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Business::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「資格」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCertification(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Certification::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「連絡先電話番号：続柄・関係」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateContactRelationship(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && ContactRelationship::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「契約状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateContractStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && ContractStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「上限管理結果」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCopayCoordinationResult(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && CopayCoordinationResult::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「上限管理区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCopayCoordinationType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && CopayCoordinationType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「曜日」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDayOfWeek(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DayOfWeek::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「公費制度（法別番号）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDefrayerCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DefrayerCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者負担上限額管理結果票：作成区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingCopayCoordinationExchangeAim(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsBillingCopayCoordinationExchangeAim::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス：明細書：上限管理区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatementCopayCoordinationStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsBillingStatementCopayCoordinationStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス：請求：状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsBillingStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス受給者証 サービス内容」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsCertificationAgreementType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsCertificationAgreementType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス受給者証 サービス種別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsCertificationServiceType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsCertificationServiceType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス受給者証 認定区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsCertificationStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsCertificationStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害程度区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsLevel(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsLevel::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス：計画：サービス区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsProjectServiceCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsProjectServiceCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス：予実：状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsProvisionReportStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsProvisionReportStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害福祉サービス：請求：サービス種類コード」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsServiceDivisionCode(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsServiceDivisionCode::isValid((string)$value);
    }

    /**
     * 入力値が列挙型「福祉・介護職員等特定処遇改善加算（障害）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsSpecifiedTreatmentImprovementAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && DwsSpecifiedTreatmentImprovementAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「ベースアップ等支援加算（障害）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBaseIncreaseSupportAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && DwsbaseIncreaseSupportAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者別地域加算区分（障害）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsUserLocationAddition(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsUserLocationAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「福祉・介護職員処遇改善加算（障害）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsTreatmentImprovementAddition(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsTreatmentImprovementAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「障害種別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && DwsType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「特定事業所加算区分（障害・居宅介護）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateHomeHelpServiceSpecifiedOfficeAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && HomeHelpServiceSpecifiedOfficeAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「特定事業所加算区分（介保・訪問介護）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateHomeVisitLongTermCareSpecifiedOfficeAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && HomeVisitLongTermCareSpecifiedOfficeAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「ベースアップ等支援加算（介保）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsBaseIncreaseSupportAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && LtcsBaseIncreaseSupportAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス請求状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsBillingStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsBillingStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「居宅サービス計画作成区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsCarePlanAuthorType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsCarePlanAuthorType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：明細書：中止理由」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsExpiredReason(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsExpiredReason::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険被保険者証 サービス種別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsInsCardServiceType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsInsCardServiceType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険被保険者証 認定区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsInsCardStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsInsCardStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「要介護度（要介護状態区分等）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsLevel(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsLevel::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：予実：状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsProvisionReportStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsProvisionReportStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：計画：サービス提供量」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsProjectAmountCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsProjectAmountCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：計画：サービス区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsProjectServiceCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsProjectServiceCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：サービス区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsServiceCodeCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsServiceCodeCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護保険サービス：請求：サービス種類コード」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsServiceDivisionCode(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsServiceDivisionCode::isValid((string)$value);
    }

    /**
     * 入力値が列挙型「介護職員等特定処遇改善加算（介保）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsSpecifiedTreatmentImprovementAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && LtcsSpecifiedTreatmentImprovementAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「介護職員処遇改善加算（介保）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsTreatmentImprovementAddition(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsTreatmentImprovementAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者別地域加算区分（介保）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsUserLocationAddition(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsUserLocationAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「地域加算（介保）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeLocationAddition(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && LtcsOfficeLocationAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「事業所：状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && OfficeStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「支払方法」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePaymentMethod(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value)
            && PaymentMethod::isValid((int)$value)
            && PaymentMethod::none()->value() !== $value;
    }

    /**
     * 入力値が列挙型「権限」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePermissions(string $attribute, $value, array $parameters): bool
    {
        return Map::from($value)->keys()->forAll(fn ($x): bool => is_string($x) && Permission::isValid($x));
    }

    /**
     * 入力値が列挙型「権限」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePermission(string $attribute, $value, array $parameters): bool
    {
        return is_string($value) && Permission::isValid((string)$value);
    }

    /**
     * 入力値が列挙型「都道府県」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePrefecture(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value)
            && Prefecture::isValid((int)$value)
            && Prefecture::none()->value() !== $value;
    }

    /**
     * 入力値が列挙型「事業所区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePurpose(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Purpose::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「事業所：指定区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeQualification(string $attribute, $value, array $parameters): bool
    {
        return is_string($value) && OfficeQualification::isValid((string)$value);
    }

    /**
     * 入力値が列挙型「繰り返し周期」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateRecurrence(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Recurrence::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「権限範囲」を表す値であることを検証する
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateRoleScope(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && RoleScope::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「端数処理区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateRounding(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value)
            && Rounding::isValid((int)$value)
            && Rounding::none()->value() !== $value;
    }

    /**
     * 入力値が列挙型「サービスオプション」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateServiceOption(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && ServiceOption::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「事業領域」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateServiceSegment(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && ServiceSegment::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「性別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateSex(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Sex::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「スタッフ：状態」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateStaffStatus(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && StaffStatus::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「給付方式」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateSubsidyType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && UserDwsSubsidyType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「勤務区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateTask(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Task::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「税率区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateTaxCategory(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && TaxCategory::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「課税区分」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateTaxType(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && TaxType::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「時間帯」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateTimeframe(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && Timeframe::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「特定事業所加算区分（障害・重度訪問介護）」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateVisitingCareForPwsdSpecifiedOfficeAddition(
        string $attribute,
        $value,
        array $parameters
    ): bool {
        return is_numeric($value) && VisitingCareForPwsdSpecifiedOfficeAddition::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者請求：請求結果」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingResult(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && UserBillingResult::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者請求：利用サービス」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingUsedService(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value) && UserBillingUsedService::isValid((int)$value);
    }

    /**
     * 入力値が列挙型「利用者：自治体助成情報：基準値種別」を表す値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserDwsSubsidyFactor(string $attribute, $value, array $parameters): bool
    {
        return is_numeric($value)
            && UserDwsSubsidyFactor::isValid((int)$value)
            && UserDwsSubsidyFactor::none()->value() !== $value;
    }
}
