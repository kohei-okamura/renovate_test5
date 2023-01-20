<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画更新リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read int $staffId
 * @property-read string $writtenOn
 * @property-read string $effectivatedOn
 * @property-read string $requestFromUser
 * @property-read string $requestFromFamily
 * @property-read array $programs
 * @property-read string $objective
 */
class UpdateDwsProjectRequest extends StaffRequest implements ValidatesWhenResolved
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
            'objective' => $this->objective,
            'programs' => Seq::fromArray($this->programs)
                ->map(fn (array $program): DwsProjectProgram => DwsProjectProgram::create([
                    'summaryIndex' => $program['summaryIndex'],
                    'category' => DwsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): DwsProjectContent => DwsProjectContent::create([
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
    protected function rules(array $input): array
    {
        return [
            'officeId' => ['required', 'office_exists:' . Permission::updateDwsProjects()],
            'staffId' => ['required', 'staff_exists:' . Permission::updateDwsProjects()],
            'writtenOn' => ['required', 'date'],
            'effectivatedOn' => ['required', 'date'],
            'requestFromUser' => ['required', 'string'],
            'requestFromFamily' => ['required', 'string'],
            'objective' => ['required', 'string'],
            'programs' => ['required', 'array'],
            'programs.*.summaryIndex' => ['required', 'integer'],
            'programs.*.category' => ['required', 'dws_project_service_category'],
            'programs.*.recurrence' => ['required', 'recurrence'],
            'programs.*.dayOfWeeks' => ['required', 'array'],
            'programs.*.dayOfWeeks.*' => ['required', 'day_of_week'],
            'programs.*.slot.start' => ['required', 'date_format:H:i'],
            'programs.*.slot.end' => ['required', 'date_format:H:i', 'after:programs.*.slot.start'],
            'programs.*.headcount' => ['required', 'integer'],
            'programs.*.ownExpenseProgramId' => [
                'nullable',
                'own_expense_program_exists:' . Permission::updateDwsProjects(),
                'own_expense_program_belongs_to_office:officeId,' . Permission::updateDwsProjects(),
            ],
            'programs.*.options' => ['nullable', 'array'],
            'programs.*.options.*' => ['required', 'service_option', 'dws_project_service_option'],
            'programs.*.contents.*.menuId' => ['required', 'dws_project_service_menu_exists'],
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
        ];
    }
}
