<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests\Delegates;

use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\ProvisionReport\GetLtcsProvisionReportUseCase;

/**
 * 介護保険サービス：予実フォーム.
 */
class LtcsProvisionReportFormDelegateImpl implements LtcsProvisionReportFormDelegate
{
    /** {@inheritdoc} */
    public function convertEntryArrayToModel(array $entries): array
    {
        return Seq::fromArray($entries)
            ->map(fn (array $entry): LtcsProvisionReportEntry => LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => $entry['ownExpenseProgramId'] ?? null,
                'slot' => TimeRange::create([
                    'start' => $entry['slot']['start'],
                    'end' => $entry['slot']['end'],
                ]),
                'timeframe' => Timeframe::from($entry['timeframe']),
                'category' => LtcsProjectServiceCategory::from($entry['category']),
                'amounts' => Seq::fromArray($entry['amounts'])
                    ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                        'category' => LtcsProjectAmountCategory::from($amount['category']),
                        'amount' => $amount['amount'],
                    ]))
                    ->toArray(),
                'headcount' => $entry['headcount'],
                'serviceCode' => isset($entry['serviceCode']) ? ServiceCode::fromString($entry['serviceCode']) : null,
                'options' => Seq::fromArray($entry['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $entry['note'] ?? '',
                'plans' => empty($entry['plans'])
                    ? []
                    : Seq::fromArray($entry['plans'] ?: [])
                        ->map(fn (string $plan): Carbon => Carbon::parse($plan))
                        ->toArray(),
                'results' => empty($entry['results'])
                    ? []
                    : Seq::fromArray($entry['results'] ?: [])
                        ->map(fn (string $result): Carbon => Carbon::parse($result))
                        ->toArray(),
            ]))
            ->toArray();
    }

    /** {@inheritdoc} */
    public function setValidator(Context $context, Validator $validator): void
    {
        $validator->after(function (Validator $validator) use ($context): void {
            if ($validator->messages()->isNotEmpty()) {
                // すでにfailの場合は実行しない
                return;
            }
            $data = $validator->getData();
            $officeId = (int)$data['officeId'];
            $userId = (int)$data['userId'];
            $providedIn = $data['providedIn'];

            $this->getEntity($context, $officeId, $userId, $providedIn)
                ->map(function (LtcsProvisionReport $x) use ($validator): void {
                    if ($x->status === LtcsProvisionReportStatus::fixed()) {
                        $validator->errors()->add('entries', '確定済みの予実は編集できません。');
                    }
                })
                ->getOrElse(function (): void {
                    // 何もしない
                });
        });
    }

    /** {@inheritdoc} */
    public function createRules(array $input): array
    {
        $ownExpense = LtcsProjectServiceCategory::ownExpense()->value();
        $officeId = Arr::get($input, 'officeId');
        return [
            'entries' => ['required', 'array'],
            'entries.*.ownExpenseProgramId' => [
                "prohibited_unless:entries.*.category,{$ownExpense}",
                "required_if:entries.*.category,{$ownExpense}",
                'nullable',
                'own_expense_program_exists:' . Permission::updateLtcsProvisionReports(),
                "own_expense_program_belongs_to_office:{$officeId}," . Permission::updateLtcsProvisionReports(),
            ],
            'entries.*.slot' => ['required', 'array'],
            'entries.*.slot.start' => ['required', 'date_format:H:i'],
            'entries.*.slot.end' => ['required', 'date_format:H:i'],
            'entries.*.timeframe' => ['required', 'timeframe'],
            'entries.*.category' => ['required', 'ltcs_project_service_category'],
            'entries.*.amounts' => [
                "prohibited_if:entries.*.category,{$ownExpense}",
                "required_unless:entries.*.category,{$ownExpense}",
                'nullable',
                'array',
            ],
            'entries.*.amounts.*.category' => ['required', 'ltcs_project_amount_category'],
            'entries.*.amounts.*.amount' => ['required', 'integer'],
            'entries.*.headcount' => ['required', 'integer'],
            'entries.*.serviceCode' => [
                "prohibited_if:entries.*.category,{$ownExpense}",
                "required_unless:entries.*.category,{$ownExpense}",
                'nullable',
                'string',
                'max:6',
                'regex:/[A-Z0-9]/',
            ],
            'entries.*.options' => ['nullable', 'array'],
            'entries.*.options.*' => ['required', 'service_option', 'ltcs_provision_report_service_option'],
            'entries.*.note' => ['nullable', 'string'],
            'entries.*.plans' => ['nullable', 'array'],
            'entries.*.plans.*' => ['required', 'date', 'entries_have_no_overlapped_range:plans'],
            'entries.*.results' => ['required_without:entries.*.plans', 'nullable', 'array'],
            'entries.*.results.*' => ['required', 'date', 'entries_have_no_overlapped_range:results'],
            'specifiedOfficeAddition' => ['required', 'home_visit_long_term_care_specified_office_addition'],
            'treatmentImprovementAddition' => ['required', 'ltcs_treatment_improvement_addition'],
            'specifiedTreatmentImprovementAddition' => ['required', 'ltcs_specified_treatment_improvement_addition'],
            'baseIncreaseSupportAddition' => ['required', 'ltcs_base_increase_support_addition'],
            'locationAddition' => ['required', 'office_location_addition'],
        ];
    }

    /** {@inheritdoc} */
    public function getAttributes(): array
    {
        return [
            'entries.*.slot.start' => '時間帯 開始時刻',
            'entries.*.plans' => '予定年月日',
            'entries.*.results' => '実績年月日',
        ];
    }

    /** {@inheritdoc} */
    public function getErrorMessages(): array
    {
        return [
            'entries.*.plans.*.entries_have_no_overlapped_range' => '予定が重複しています。',
            'entries.*.results.*.entries_have_no_overlapped_range' => '実績が重複しています。',
            'entries.*.ownExpenseProgramId.required_if' => '入力してください。',
            'entries.*.amounts.required_unless' => '入力してください。',
            'entries.*.serviceCode.required_unless' => '入力してください。',
        ];
    }

    /**
     * 介護保険サービス：予実取得ユースケースを取得.
     *
     * @return \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase
     */
    private function getUseCase(): GetLtcsProvisionReportUseCase
    {
        return app(GetLtcsProvisionReportUseCase::class);
    }

    /**
     * エンティティを取得.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @return \ScalikePHP\Option
     */
    private function getEntity(Context $context, int $officeId, int $userId, string $providedIn): Option
    {
        return $this->getUseCase()->handle(
            $context,
            Permission::updateLtcsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn)
        );
    }
}
