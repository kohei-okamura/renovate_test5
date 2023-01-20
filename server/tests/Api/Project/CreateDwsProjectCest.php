<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Project;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProject Create のテスト.
 * POST /users/{userId}/dws-Projects
 */
class CreateDwsProjectCest extends DwsProjectTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]);

        $I->sendPOST("users/{$this->examples->dwsProjects[0]->userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：計画が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 自費サービスを設定できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenOwnExpenseProgramIsSet(ApiTester $I)
    {
        $I->wantTo('succeed API call when OwnExpenseProgram is set');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]->copy([
            'programs' => [
                DwsProjectProgram::create([
                    'summaryIndex' => 1,
                    'category' => DwsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::tue(),
                        DayOfWeek::thu(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'headcount' => 2,
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'options' => [],
                    'contents' => [
                        DwsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => '備考',
                ]),
            ],
        ]));

        $I->sendPost("users/{$this->examples->dwsProjects[0]->userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：計画が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 他事業所の自費サービスを指定した場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOwnExpenseProgramIdBelongsToOtherOffice(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when ownExpenseProgramId belongs to other office');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]->copy([
            'programs' => [
                DwsProjectProgram::create([
                    'summaryIndex' => 1,
                    'category' => DwsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::tue(),
                        DayOfWeek::thu(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'headcount' => 2,
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id,
                    'options' => [],
                    'contents' => [
                        DwsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => '備考',
                ]),
            ],
        ]));

        $I->sendPost("users/{$this->examples->dwsProjects[0]->userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['programs.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 利用者が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]);
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 「サービスオプション」が「障害福祉サービス：計画」の「サービスオプション」として不正の場合400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenServiceOptionIsInvalidForDwsProject(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when serviceOption is invalid for DwsProject');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]->copy([
            'programs' => [$this->examples->dwsProjects[0]->programs[0]->copy([
                'category' => DwsProjectServiceCategory::physicalCare(),
                'options' => [ServiceOption::notificationEnabled()],
            ])],
        ]));

        $I->sendPOST("users/{$this->examples->dwsProjects[0]->userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['programs.0.options.0' => ['正しいサービスオプションを指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]);

        $I->sendPOST('users/userId/dws-projects', $dwsProject);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが同じ事業者に存在しない場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]);
        $userId = $this->examples->users[14]->id;

        $I->sendPOST("users/{$userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $dwsProject = $this->defaultParam($this->examples->dwsProjects[0]);

        $I->sendPOST("users/{$this->examples->dwsProjects[0]->userId}/dws-projects", $dwsProject);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成
     *
     * @param DwsProject $dwsProject
     * @return array
     */
    private function defaultParam(DwsProject $dwsProject): array
    {
        return [
            'officeId' => $dwsProject->officeId,
            'staffId' => $dwsProject->staffId,
            'writtenOn' => $dwsProject->writtenOn->toDateString(),
            'effectivatedOn' => $dwsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $dwsProject->requestFromUser,
            'requestFromFamily' => $dwsProject->requestFromFamily,
            'objective' => $dwsProject->objective,
            'programs' => Seq::fromArray($dwsProject->programs)
                ->map(fn (DwsProjectProgram $program): array => [
                    'summaryIndex' => $program->summaryIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (DwsProjectContent $content): array => [
                            'menuId' => $content->menuId,
                            'duration' => $content->duration,
                            'content' => $content->content,
                            'memo' => $content->memo,
                        ])
                        ->toArray(),
                ])
                ->toArray(),
        ];
    }
}
