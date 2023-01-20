<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\Objective;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：計画更新リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read int $staffId
 * @property-read string $writtenOn
 * @property-read string $effectivatedOn
 * @property-read string $requestFromUser
 * @property-read string $requestFromFamily
 * @property-read string $problem
 * @property-read array $programs
 * @property-read array $longTermObjective
 * @property-read array $shortTermObjective
 */
class UpdateLtcsProjectRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'officeId' => $this->officeId,
            'staffId' => $this->staffId,
            'writtenOn' => Carbon::parse($this->writtenOn),
            'effectivatedOn' => Carbon::parse($this->effectivatedOn),
            'requestFromUser' => $this->requestFromUser,
            'requestFromFamily' => $this->requestFromFamily,
            'problem' => $this->problem,
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($this->longTermObjective['term']['start']),
                    'end' => Carbon::parse($this->longTermObjective['term']['end']),
                ]),
                'text' => $this->longTermObjective['text'],
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($this->shortTermObjective['term']['start']),
                    'end' => Carbon::parse($this->shortTermObjective['term']['end']),
                ]),
                'text' => $this->shortTermObjective['text'],
            ]),
            'programs' => Seq::fromArray($this->programs)
                ->map(fn (array $program): LtcsProjectProgram => LtcsProjectProgram::create([
                    'programIndex' => $program['programIndex'],
                    'category' => LtcsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'timeframe' => Timeframe::from($program['timeframe']),
                    'amounts' => Seq::fromArray($program['amounts'])
                        ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]))
                        ->toArray(),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'serviceCode' => ServiceCode::fromString($program['serviceCode']),
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): LtcsProjectContent => LtcsProjectContent::create([
                            'menuId' => $content['menuId'],
                            'duration' => $content['duration'],
                            'content' => $content['content'],
                            'memo' => $content['memo'] ?? '',
                        ]))
                        ->toArray(),
                    'note' => $program['note'] ?? '',
                ]))
                ->toArray(),
        ];
    }

    /** {@inheritdoc} */
    public function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::updateLtcsProjects()],
            'staffId' => ['required', 'staff_exists:' . Permission::updateLtcsProjects()],
            'writtenOn' => ['required', 'date'],
            'effectivatedOn' => ['required', 'date'],
            'requestFromUser' => ['required', 'string'],
            'requestFromFamily' => ['required', 'string'],
            'problem' => ['required', 'string'],
            'longTermObjective.term.start' => ['required', 'date'],
            'longTermObjective.term.end' => ['required', 'date', 'after:longTermObjective.term.start'],
            'longTermObjective.text' => ['required', 'string'],
            'shortTermObjective.term.start' => ['required', 'date'],
            'shortTermObjective.term.end' => ['required', 'date', 'after:shortTermObjective.term.start'],
            'shortTermObjective.text' => ['required', 'string'],
            'programs' => ['required', 'array'],
            'programs.*.programIndex' => ['required', 'integer'],
            'programs.*.category' => ['required', 'ltcs_project_service_category'],
            'programs.*.recurrence' => ['required', 'recurrence'],
            'programs.*.dayOfWeeks' => ['required', 'array'],
            'programs.*.dayOfWeeks.*' => ['required', 'day_of_week'],
            'programs.*.slot.start' => ['required', 'date_format:H:i'],
            'programs.*.slot.end' => ['required', 'date_format:H:i', 'after:programs.*.slot.start'],
            'programs.*.timeframe' => ['required', 'timeframe'],
            'programs.*.amounts' => ['required', 'array'],
            'programs.*.amounts.*.category' => ['required', 'ltcs_project_amount_category'],
            'programs.*.amounts.*.amount' => ['required', 'integer'],
            'programs.*.headcount' => ['required', 'integer'],
            'programs.*.ownExpenseProgramId' => [
                'nullable',
                'own_expense_program_exists:' . Permission::updateLtcsProjects(),
                'own_expense_program_belongs_to_office:officeId,' . Permission::updateLtcsProjects(),
            ],
            'programs.*.serviceCode' => ['string', 'max:6', 'regex:/[A-Z0-9]/'],
            'programs.*.options' => ['nullable', 'array'],
            'programs.*.options.*' => ['required', 'service_option', 'ltcs_project_service_option'],
            'programs.*.contents.*.menuId' => ['required', 'ltcs_project_service_menu_exists'],
            'programs.*.contents.*.duration' => ['required', 'integer'],
            'programs.*.contents.*.content' => ['required', 'string'],
            'programs.*.contents.*.memo' => ['nullable', 'string'],
            'programs.*.note' => ['nullable', 'string'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'programs.*.slot.start' => '時間帯 開始時刻',
            'programs.*.slot.end' => '時間帯 終了時刻',
            'longTermObjective.term.start' => '長期目標 開始日',
            'shortTermObjective.term.start' => '短期目標 開始日',
        ];
    }
}
