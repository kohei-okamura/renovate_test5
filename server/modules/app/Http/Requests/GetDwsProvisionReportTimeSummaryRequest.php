<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：予実の合計時間数取得リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 * @property-read array $plans
 * @property-read array $results
 */
class GetDwsProvisionReportTimeSummaryRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 処理用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'officeId' => $this->officeId,
            'userId' => $this->userId,
            'providedIn' => Carbon::parse($this->providedIn),
            'plans' => Seq::fromArray($this->plans)
                ->map(fn (array $plan): DwsProvisionReportItem => DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::parse($plan['schedule']['date']),
                        'start' => Carbon::parse($plan['schedule']['start']),
                        'end' => Carbon::parse($plan['schedule']['end']),
                    ]),
                    'category' => DwsProjectServiceCategory::from($plan['category']),
                    'headcount' => $plan['headcount'],
                    'movingDurationMinutes' => $plan['movingDurationMinutes'] ?? 0,
                    'ownExpenseProgramId' => $plan['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($plan['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'note' => $plan['note'] ?? '',
                ]))
                ->toArray(),
            'results' => Seq::fromArray($this->results)
                ->map(fn (array $result): DwsProvisionReportItem => DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::parse($result['schedule']['date']),
                        'start' => Carbon::parse($result['schedule']['start']),
                        'end' => Carbon::parse($result['schedule']['end']),
                    ]),
                    'category' => DwsProjectServiceCategory::from($result['category']),
                    'headcount' => $result['headcount'],
                    'movingDurationMinutes' => $result['movingDurationMinutes'] ?? 0,
                    'ownExpenseProgramId' => $result['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($result['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'note' => $result['note'] ?? '',
                ]))
                ->toArray(),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $ownExpense = DwsProjectServiceCategory::ownExpense()->value();
        $officeId = Arr::get($input, 'officeId');
        return [
            'officeId' => ['required', 'office_exists:' . Permission::updateDwsProvisionReports()],
            'userId' => ['required', 'user_exists:' . Permission::updateDwsProvisionReports()],
            'providedIn' => ['required', 'date_format:Y-m'],
            'plans' => ['required_without:results', 'array'],
            'plans.*.schedule' => [
                'required',
                'array',
                'no_schedule_duplicated:plans',
                'no_schedule_overlapped:plans',
            ],
            'plans.*.schedule.date' => ['required', 'date'],
            'plans.*.schedule.start' => ['required', 'date'],
            'plans.*.schedule.end' => ['required', 'bail', 'date', 'after:plans.*.schedule.start'],
            'plans.*.category' => ['required', 'dws_project_service_category'],
            'plans.*.headcount' => ['required', 'integer'],
            'plans.*.movingDurationMinutes' => ['nullable', 'integer'],
            'plans.*.ownExpenseProgramId' => [
                "prohibited_unless:plans.*.category,{$ownExpense}",
                "required_if:plans.*.category,{$ownExpense}",
                'nullable',
                'own_expense_program_exists:' . Permission::updateDwsProvisionReports(),
                "own_expense_program_belongs_to_office:{$officeId}," . Permission::updateDwsProvisionReports(),
            ],
            'plans.*.options' => ['nullable', 'array'],
            'plans.*.options.*' => ['required', 'service_option', 'dws_provision_report_service_option:plans'],
            'plans.*.note' => ['nullable', 'string'],
            'results' => ['nullable', 'array'],
            'results.*.schedule' => [
                'required',
                'array',
                'no_schedule_duplicated:results',
                'no_schedule_overlapped:results',
            ],
            'results.*.schedule.date' => ['required', 'date'],
            'results.*.schedule.start' => ['required', 'date'],
            'results.*.schedule.end' => ['required', 'bail', 'date', 'after:results.*.schedule.start'],
            'results.*.category' => ['required', 'dws_project_service_category'],
            'results.*.headcount' => ['required', 'integer'],
            'results.*.movingDurationMinutes' => ['nullable', 'integer'],
            'results.*.ownExpenseProgramId' => [
                "prohibited_unless:results.*.category,{$ownExpense}",
                "required_if:results.*.category,{$ownExpense}",
                'nullable',
                'own_expense_program_exists:' . Permission::updateDwsProvisionReports(),
                "own_expense_program_belongs_to_office:{$officeId}," . Permission::updateDwsProvisionReports(),
            ],
            'results.*.options' => ['nullable', 'array'],
            'results.*.options.*' => ['required', 'service_option', 'dws_provision_report_service_option:results'],
            'results.*.note' => ['nullable', 'string'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'plans.*.category' => 'サービス区分',
            'plans.*.schedule.start' => 'スケジュール 開始時刻',
            'results.*.category' => 'サービス区分',
            'results.*.schedule.start' => 'スケジュール 開始時刻',
        ];
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return [
            'plans.required_without' => '予定または実績のいずれかは入力する必要があります。',
            'plans.*.schedule.no_schedule_duplicated' => '時間帯が完全に一致する予定が存在します。',
            'plans.*.schedule.no_schedule_overlapped' => '時間帯が重複する予定が存在します。',
            'plans.*.ownExpenseProgramId.required_if' => '入力してください。',
            'results.*.schedule.no_schedule_duplicated' => '時間帯が完全に一致する実績が存在します。',
            'results.*.schedule.no_schedule_overlapped' => '時間帯が重複する実績が存在します。',
            'results.*.ownExpenseProgramId.required_if' => '入力してください。',
        ];
    }
}
