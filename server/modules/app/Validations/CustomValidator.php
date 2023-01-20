<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use App\Validations\Rules\AllPaymentMethodsAreWithdrawalRule;
use App\Validations\Rules\AllUserBillingsArePendingRule;
use App\Validations\Rules\AsciiAlphaNumRule;
use App\Validations\Rules\AuthorizedPermissionRule;
use App\Validations\Rules\AuthorizedPermissionsRule;
use App\Validations\Rules\BankAccountNumberDigitsRule;
use App\Validations\Rules\BooleanExtRule;
use App\Validations\Rules\ConfirmedIdRule;
use App\Validations\Rules\CopayListCanDownloadRule;
use App\Validations\Rules\CopayUnderCopayLimitRule;
use App\Validations\Rules\DurationsEqualToScheduleRule;
use App\Validations\Rules\DwsBillingCanCopyRule;
use App\Validations\Rules\DwsBillingServiceReportStatusCanBulkUpdateRule;
use App\Validations\Rules\DwsBillingServiceReportStatusCanUpdateRule;
use App\Validations\Rules\DwsBillingStatementCanRefreshRule;
use App\Validations\Rules\DwsCertificationAgreementTypeDwsLevelRule;
use App\Validations\Rules\DwsCertificationBelongsToUserRule;
use App\Validations\Rules\DwsCertificationGrantExclusiveRule;
use App\Validations\Rules\DwsCertificationNotBelongToBillingRule;
use App\Validations\Rules\DwsContractCanBeTerminatedRule;
use App\Validations\Rules\DwsProjectServiceOptionRule;
use App\Validations\Rules\DwsProvisionReportServiceOptionRule;
use App\Validations\Rules\EmailAddressIsNotUsedByAnyStaffRule;
use App\Validations\Rules\EntriesHaveNoOverlappedRangeRule;
use App\Validations\Rules\EqualToLengthOfRule;
use App\Validations\Rules\ExcelTimestampRule;
use App\Validations\Rules\HasActiveCertificationAgreementsRule;
use App\Validations\Rules\HasActiveCertificationGrantRule;
use App\Validations\Rules\HaveIntegrityOfRule;
use App\Validations\Rules\InvitationEmailAddressIsNotUsedByAnyStaffRule;
use App\Validations\Rules\InvitationTokenMatchRule;
use App\Validations\Rules\KatakanaRule;
use App\Validations\Rules\LtcsBillingStatementCanRefreshRule;
use App\Validations\Rules\LtcsContractCanBeTerminatedRule;
use App\Validations\Rules\LtcsInsCardNotBelongToBillingRule;
use App\Validations\Rules\LtcsProjectServiceOptionRule;
use App\Validations\Rules\LtcsProvisionReportContainsLtcsServiceRule;
use App\Validations\Rules\LtcsProvisionReportServiceOptionRule;
use App\Validations\Rules\LtcsServiceCodeSpecifiedOfficeMatchRule;
use App\Validations\Rules\NoConflictRule;
use App\Validations\Rules\NoDwsCertificationGrantsDuplicateRule;
use App\Validations\Rules\NoLtcsInsCardThreeMoreThanValidRule;
use App\Validations\Rules\NonRelationToOfficesRule;
use App\Validations\Rules\NoOverlapWithPreviousMonthRule;
use App\Validations\Rules\NoOvertimeRule;
use App\Validations\Rules\NoScheduleDuplicatedRule;
use App\Validations\Rules\NoScheduleOverlappedRule;
use App\Validations\Rules\NotOnlyCopayCoordinationOfficeRule;
use App\Validations\Rules\NotParentOfficeGroupRule;
use App\Validations\Rules\OfficeHasDwsGenericServiceRule;
use App\Validations\Rules\OnlyCopayCoordinationOfficeRule;
use App\Validations\Rules\OverMaxBenefitQuotaScoreUnderBenefitScoreRule;
use App\Validations\Rules\OverMaxBenefitScoreUnderManagedScoreRule;
use App\Validations\Rules\OwnExpenseProgramBelongsToOfficeRule;
use App\Validations\Rules\PhoneNumberRule;
use App\Validations\Rules\StartOfDwsContractPeriodFilledRule;
use App\Validations\Rules\StartOfLtcsContractPeriodFilledRule;
use App\Validations\Rules\UserBelongsToOfficeRule;
use App\Validations\Rules\UserBillingAmountIsNonNegativeRule;
use App\Validations\Rules\UserBillingBankAccountNumberDigitsRule;
use App\Validations\Rules\UserBillingCanCreateNoticeRule;
use App\Validations\Rules\UserBillingCanUpdateRule;
use App\Validations\Rules\UserBillingChangeablePaymentMethodRule;
use App\Validations\Rules\UserBillingDepositCanDeleteRule;
use App\Validations\Rules\UserBillingDepositCanUpdateRule;
use App\Validations\Rules\UserBillingDepositRequiredRule;
use App\Validations\Rules\UserBillingHasOtherThanOwnExpenseServiceRule;
use App\Validations\Rules\UserBillingResultIsNotNoneRule;
use App\Validations\Rules\UserBillingWhoseAmountGreaterThanZeroExistsRule;
use App\Validations\Rules\UserStatusCanUpdateToFalseRule;
use App\Validations\Rules\ZenginDataRecordCharRule;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Entity;
use Domain\Permission\Permission;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Domain\Staff\Staff;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Lib\Exceptions\InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use ScalikePHP\Seq;
use UseCase\Shift\FindShiftUseCase;
use UseCase\Shift\LookupAttendanceUseCase;
use UseCase\Shift\LookupShiftUseCase;
use UseCase\Staff\FindStaffUseCase;
use UseCase\Staff\LookupStaffUseCase;

/**
 * CustomValidator.
 */
final class CustomValidator extends Validator
{
    use BillingValidator;
    use EntityExistsValidator;
    use EnumValidator;

    use AllPaymentMethodsAreWithdrawalRule;
    use AllUserBillingsArePendingRule;
    use AsciiAlphaNumRule;
    use AuthorizedPermissionRule;
    use AuthorizedPermissionsRule;
    use BankAccountNumberDigitsRule;
    use BooleanExtRule;
    use ConfirmedIdRule;
    use CopayListCanDownloadRule;
    use CopayUnderCopayLimitRule;
    use DurationsEqualToScheduleRule;
    use DwsBillingCanCopyRule;
    use DwsBillingServiceReportStatusCanBulkUpdateRule;
    use DwsBillingServiceReportStatusCanUpdateRule;
    use DwsBillingStatementCanRefreshRule;
    use DwsCertificationAgreementTypeDwsLevelRule;
    use DwsCertificationBelongsToUserRule;
    use DwsCertificationGrantExclusiveRule;
    use DwsCertificationNotBelongToBillingRule;
    use DwsContractCanBeTerminatedRule;
    use DwsProjectServiceOptionRule;
    use DwsProvisionReportServiceOptionRule;
    use EmailAddressIsNotUsedByAnyStaffRule;
    use EntriesHaveNoOverlappedRangeRule;
    use EqualToLengthOfRule;
    use ExcelTimestampRule;
    use HasActiveCertificationAgreementsRule;
    use HasActiveCertificationGrantRule;
    use HaveIntegrityOfRule;
    use InvitationEmailAddressIsNotUsedByAnyStaffRule;
    use InvitationTokenMatchRule;
    use KatakanaRule;
    use LtcsBillingStatementCanRefreshRule;
    use LtcsContractCanBeTerminatedRule;
    use LtcsInsCardNotBelongToBillingRule;
    use LtcsProjectServiceOptionRule;
    use LtcsProvisionReportContainsLtcsServiceRule;
    use LtcsProvisionReportServiceOptionRule;
    use LtcsServiceCodeSpecifiedOfficeMatchRule;
    use NoConflictRule;
    use NoDwsCertificationGrantsDuplicateRule;
    use NoLtcsInsCardThreeMoreThanValidRule;
    use NonRelationToOfficesRule;
    use NoOverlapWithPreviousMonthRule;
    use NoOvertimeRule;
    use NoScheduleDuplicatedRule;
    use NoScheduleOverlappedRule;
    use NotOnlyCopayCoordinationOfficeRule;
    use NotParentOfficeGroupRule;
    use OfficeHasDwsGenericServiceRule;
    use OnlyCopayCoordinationOfficeRule;
    use OverMaxBenefitQuotaScoreUnderBenefitScoreRule;
    use OverMaxBenefitScoreUnderManagedScoreRule;
    use OwnExpenseProgramBelongsToOfficeRule;
    use PhoneNumberRule;
    use StartOfDwsContractPeriodFilledRule;
    use StartOfLtcsContractPeriodFilledRule;
    use UserBelongsToOfficeRule;
    use UserBillingAmountIsNonNegativeRule;
    use UserBillingBankAccountNumberDigitsRule;
    use UserBillingCanCreateNoticeRule;
    use UserBillingCanUpdateRule;
    use UserBillingChangeablePaymentMethodRule;
    use UserBillingDepositCanDeleteRule;
    use UserBillingDepositCanUpdateRule;
    use UserBillingDepositRequiredRule;
    use UserBillingHasOtherThanOwnExpenseServiceRule;
    use UserBillingResultIsNotNoneRule;
    use UserBillingWhoseAmountGreaterThanZeroExistsRule;
    use UserStatusCanUpdateToFalseRule;
    use ZenginDataRecordCharRule;

    protected Context $context;

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \App\Validations\CustomValidator
     */
    public static function make(
        Context $context,
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): self {
        return tap(
            new self(app('translator'), $data, $rules, $messages, $customAttributes),
            function (CustomValidator $validator) use ($context): void {
                $validator->setContext($context);
            }
        );
    }

    /**
     * 勤務シフト更新時に、スタッフの勤務シフトがダブルブッキングにならないことを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateAvailable(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(3, $parameters, 'available');
        $assigneeId = $value;
        $start = Arr::get($this->data, $parameters[0]);
        $end = Arr::get($this->data, $parameters[1]);
        $shiftId = (int)$parameters[2];
        // 開始日時と終了日時の整合性が合わない場合、このバリデーションではエラーとしない
        if ($start > $end) {
            return true;
        }

        $useCase = app(FindShiftUseCase::class);
        assert($useCase instanceof FindShiftUseCase);
        /** @var \Domain\Shift\Shift[] $shifts */
        $shifts = $useCase->handle(
            $this->context,
            Permission::updateShifts(),
            ['assigneeId' => $assigneeId, 'isConfirmed' => true],
            ['all' => true]
        )->list;
        foreach ($shifts as $shift) {
            if ($shift->id === $shiftId) {
                continue;
            } elseif ($end > $shift->schedule->start && $start < $shift->schedule->end) {
                return false;
            }
        }
        return true;
    }

    /**
     * 入力値のExcelタイムスタンプが指定日以降の日付であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateExcelTimestampAfterOrEqual(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'excel_timestamp_after_or_equal');
        if (!$this->validateExcelTimestamp($attribute, $value, $parameters)) {
            return false;
        }
        $date = Carbon::parse(Date::excelToDateTimeObject($value));
        return !$date->isBefore(Carbon::create($parameters[0]));
    }

    /**
     * 入力値の「勤務シフトID」がキャンセル済みでないことを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateNonCanceled(string $attribute, $value, array $parameters): bool
    {
        $seq = Seq::fromArray(is_array($value) ? $value : [$value]);
        if ($seq->exists(fn ($x): bool => !is_numeric($x))) {
            return false;
        }

        $this->requireParameterCount(2, $parameters, 'non_canceled');
        $permission = Permission::from((string)$parameters[1]);
        $className = $parameters[0];
        $useCase = app($className);
        if (!($useCase instanceof LookupShiftUseCase || $useCase instanceof LookupAttendanceUseCase)) {
            throw new InvalidArgumentException("{$className} is not instance of LookupUseCase");
        }
        $entityCount = $useCase
            ->handle($this->context, $permission, ...$seq->map(fn ($x): int => (int)$x)->toArray())
            ->filter(fn (Entity $x): bool => $x->isCanceled === false)->count();
        return $seq->count() === $entityCount;
    }

    /**
     * 入力値が 3桁-4桁 形式の郵便番号であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validatePostcode(string $attribute, $value, array $parameters): bool
    {
        return (bool)preg_match('/\A\d{3}-\d{4}\z/', $value);
    }

    /**
     * 入力値がスタッフで未使用のRoleIdであることを検証する.
     *
     * @param string $attribute
     * @param mixed $value RoleID
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateRoleNonUsedInStaff(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'role_non_used_in_staff');
        $permission = Permission::from((string)$parameters[0]);
        $useCase = app(FindStaffUseCase::class);
        assert($useCase instanceof FindStaffUseCase);
        $userRoleIds = array_unique($useCase->handle($this->context, $permission, [], ['all' => true])->list
            ->flatMap(fn (Staff $x): array => $x->roleIds)
            ->toArray());
        return !in_array((int)$value, $userRoleIds, true);
    }

    /**
     * 入力値の日時が指定の日付と同日であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateSameDayAs(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same_day_as');
        $start = $value;
        $date = Arr::get($this->data, $parameters[0]);
        return Carbon::parse($start)->isSameDay(Carbon::parse($date));
    }

    /**
     * 入力値の日付Rangeが、指定のパラメータの日付Rangeと日数に差がないことを検証.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateSameDaysRange(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same_days_range');
        $data = Arr::get($this->data, $parameters[0]);

        // Rangeに相当してない場合はValidationしない
        if (!$this->isCarbonRangeConvertibleArray($value)) {
            return true;
        }
        if (!$this->isCarbonRangeConvertibleArray($data)) {
            return true;
        }

        return Carbon::parse($value['end'])->diffInDays(Carbon::parse($value['start']))
            === Carbon::parse($data['end'])->diffInDays(Carbon::parse($data['start']));
    }

    /**
     * 入力値が指定のパラメータと同じ曜日であることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateSameWeekday(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'same_weekday');
        $data = Arr::get($this->data, $parameters[0], '');
        if (!(is_string($data) && strtotime($data) !== false
            && is_string($value) && strtotime($value) !== false)) {
            return true;
        }

        return Carbon::parse($value)->weekday() === Carbon::parse($data)->weekday();
    }

    /**
     * 入力値の「サービスオプション」が「勤務区分」の「サービスオプション」として正しいことを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateShiftAttendanceServiceOption(string $attribute, $value, array $parameters): bool
    {
        if (!$this->validateServiceOption($attribute, $value, $parameters)) {
            return true;
        }
        $this->requireParameterCount(1, $parameters, 'shift_attendance_service_option');
        $taskValue = Arr::get($this->data, $parameters[0]);
        if (!$this->validateTask($attribute, $taskValue, $parameters)) {
            return true;
        }

        $serviceOption = ServiceOption::from($value);

        switch (Task::from($taskValue)) {
            case Task::dwsPhysicalCare():
            case Task::dwsHousework():
            case Task::dwsAccompanyWithPhysicalCare():
            case Task::dwsAccompany():
                return $serviceOption === ServiceOption::notificationEnabled()
                    || $serviceOption === ServiceOption::oneOff()
                    || $serviceOption === ServiceOption::firstTime()
                    || $serviceOption === ServiceOption::emergency()
                    || $serviceOption === ServiceOption::sucking()
                    || $serviceOption === ServiceOption::welfareSpecialistCooperation()
                    || $serviceOption === ServiceOption::plannedByNovice()
                    || $serviceOption === ServiceOption::providedByBeginner()
                    || $serviceOption === ServiceOption::providedByCareWorkerForPwsd()
                    || $serviceOption === ServiceOption::over20()
                    || $serviceOption === ServiceOption::over50();
            case Task::dwsVisitingCareForPwsd():
                return $serviceOption === ServiceOption::notificationEnabled()
                    || $serviceOption === ServiceOption::oneOff()
                    || $serviceOption === ServiceOption::firstTime()
                    || $serviceOption === ServiceOption::emergency()
                    || $serviceOption === ServiceOption::sucking()
                    || $serviceOption === ServiceOption::behavioralDisorderSupportCooperation()
                    || $serviceOption === ServiceOption::hospitalized()
                    || $serviceOption === ServiceOption::longHospitalized()
                    || $serviceOption === ServiceOption::coaching();
            case Task::ltcsPhysicalCare():
            case Task::ltcsHousework():
            case Task::ltcsPhysicalCareAndHousework():
                return $serviceOption === ServiceOption::notificationEnabled()
                    || $serviceOption === ServiceOption::oneOff()
                    || $serviceOption === ServiceOption::firstTime()
                    || $serviceOption === ServiceOption::emergency()
                    || $serviceOption === ServiceOption::over20()
                    || $serviceOption === ServiceOption::over50()
                    || $serviceOption === ServiceOption::vitalFunctionsImprovement1()
                    || $serviceOption === ServiceOption::vitalFunctionsImprovement2();
            case Task::commAccompanyWithPhysicalCare():
            case Task::commAccompany():
            case Task::comprehensive():
            case Task::ownExpense():
            case Task::fieldwork():
            case Task::assessment():
            case Task::visit():
            case Task::officeWork():
            case Task::sales():
            case Task::meeting():
            case Task::other():
                return $serviceOption === ServiceOption::notificationEnabled()
                    || $serviceOption === ServiceOption::oneOff();
            default:
                return false;
        }
    }

    /**
     * 入力値のスタッフが存在しているかつ入力された事業所と紐付いていることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateStaffBelongsToOffice(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'staff_belongs_to_office');
        $officeId = Arr::get($this->data, $parameters[0]);
        $permission = Permission::from((string)$parameters[1]);

        if (empty($officeId)) {
            return true;
        }

        $useCase = app(LookupStaffUseCase::class);
        assert($useCase instanceof LookupStaffUseCase);
        $staff = $useCase->handle($this->context, $permission, $value);
        return $staff->find(fn (Staff $x) => in_array($officeId, $x->officeIds, true))->nonEmpty();
    }

    /**
     * EqualToLengthOf ルールのプレースホルダーを置き換える.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string|string[]
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function replaceEqualToLengthOf(string $message, string $attribute, string $rule, array $parameters)
    {
        $placeholder = ':parameter';
        switch ($parameters[0]) {
            case 'assignees':
                return str_replace($placeholder, '担当スタッフ', $message);
            default:
                return $message;
        }
    }

    /**
     * ExcelTimestampAfterOrEqual ルールのプレースホルダーを置き換える.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string|string[]
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function replaceExcelTimestampAfterOrEqual(
        string $message,
        string $attribute,
        string $rule,
        array $parameters
    ) {
        $placeholder = ':parameter';
        switch ($parameters[0]) {
            case Carbon::today()->toDateString():
                return str_replace($placeholder, '今日', $message);
            default:
                return str_replace($placeholder, $parameters[0], $message);
        }
    }

    /**
     * SameDayAs ルールのプレースホルダーを置き換える.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string|string[]
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function replaceSameDayAs(string $message, string $attribute, string $rule, array $parameters)
    {
        $placeholder = ':parameter';
        switch ($parameters[0]) {
            case 'schedule.date':
                return str_replace($placeholder, '勤務日', $message);
            default:
                return $message;
        }
    }

    /**
     * SameDayAs ルールのプレースホルダーを置き換える
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string|string[]
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function replaceSameWeekday(string $message, string $attribute, string $rule, array $parameters)
    {
        $placeholder = ':parameter';
        return str_replace($placeholder, $parameters[0], $message);
    }

    /**
     * SameDaysRange ルールのプレースホルダーを置き換える
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array $parameters
     * @return string|string[]
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function replaceSameDaysRange(string $message, string $attribute, string $rule, array $parameters)
    {
        $placeholder = ':parameter';
        return str_replace($placeholder, $parameters[0], $message);
    }

    /**
     * 確定済みの勤務シフトまたは一括確定しようとしている勤務シフト内で衝突するかどうか判定する.
     *
     * @param int $shiftId
     * @param int $assigneeId
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param int[] $shiftIds
     * @return bool
     */
    private function isInConflictOfShift(
        int $shiftId,
        int $assigneeId,
        Carbon $start,
        Carbon $end,
        array $shiftIds
    ): bool {
        $useCase = app(FindShiftUseCase::class);
        assert($useCase instanceof FindShiftUseCase);
        /** @var \Domain\Shift\Shift[] $shifts */
        $shifts = $useCase
            ->handle(
                $this->context,
                Permission::updateShifts(),
                ['assigneeId' => $assigneeId],
                ['all' => true]
            )
            ->list;
        foreach ($shifts as $shift) {
            if ($shift->id === $shiftId) {
                continue;
            } elseif ($end > $shift->schedule->start && $start < $shift->schedule->end) {
                if ($shift->isConfirmed) {
                    // 確定済みと衝突する場合
                    return true;
                } elseif (in_array($shift->id, $shiftIds, true)) {
                    // 一括確定しようとしている勤務シフト内で衝突する場合
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 値がCarbonRangeドメインに変換可能な配列であるか検証.
     *
     * @param mixed $value
     * @return bool
     */
    private function isCarbonRangeConvertibleArray($value): bool
    {
        if (is_array($value)
            && isset($value['start'], $value['end'])
            && is_string($value['start'])
            && is_string($value['end'])
            && strtotime($value['start']) !== false
            && strtotime($value['end']) !== false
        ) {
            return true;
        }
        return false;
    }

    /**
     * コンテキストを設定する.
     *
     * @param \Domain\Context\Context $context
     * @return void
     */
    private function setContext(Context $context): void
    {
        $this->context = $context;
    }
}
