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
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProject create のテスト.
 * POST /users/{userId}/ltcs-projects
 */
class CreateLtcsProjectCest extends LtcsProjectTest
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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]);

        $I->sendPost("users/{$this->examples->ltcsProjects[0]->userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：計画が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 「サービスオプション」が「介護保険サービス：計画」の「サービスオプション」として不正の場合400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenServiceOptionIsInvalidForLtcsProject(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when serviceOption is invalid for LtcsProject');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]->copy([
            'programs' => [$this->examples->ltcsProjects[0]->programs[0]->copy([
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'options' => [ServiceOption::notificationEnabled()],
            ])],
        ]));

        $I->sendPOST("users/{$this->examples->ltcsProjects[0]->userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['programs.0.options.0' => ['正しいサービスオプションを指定してください。']]]);
        $I->seeLogCount(0);
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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]->copy([
            'programs' => [
                LtcsProjectProgram::create([
                    'programIndex' => 1,
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::mon(),
                        DayOfWeek::wed(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'timeframe' => Timeframe::daytime(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::housework(),
                            'amount' => 60,
                        ]),
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 60,
                        ]),
                    ],
                    'headcount' => 2,
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'serviceCode' => ServiceCode::fromString('111312'),
                    'options' => [],
                    'contents' => [
                        LtcsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                        LtcsProjectContent::create([
                            'menuId' => 2,
                            'duration' => 60,
                            'content' => '洗濯',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => '備考',
                ]),
            ],
        ]));

        $I->sendPost("users/{$this->examples->ltcsProjects[0]->userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：計画が登録されました', [
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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]->copy([
            'programs' => [
                LtcsProjectProgram::create([
                    'programIndex' => 1,
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'recurrence' => Recurrence::evenWeek(),
                    'dayOfWeeks' => [
                        DayOfWeek::mon(),
                        DayOfWeek::wed(),
                    ],
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'timeframe' => Timeframe::daytime(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::housework(),
                            'amount' => 60,
                        ]),
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 60,
                        ]),
                    ],
                    'headcount' => 2,
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id,
                    'serviceCode' => ServiceCode::fromString('111312'),
                    'options' => [],
                    'contents' => [
                        LtcsProjectContent::create([
                            'menuId' => 1,
                            'duration' => 60,
                            'content' => '掃除',
                            'memo' => '特になし',
                        ]),
                        LtcsProjectContent::create([
                            'menuId' => 2,
                            'duration' => 60,
                            'content' => '洗濯',
                            'memo' => '特になし',
                        ]),
                    ],
                    'note' => '備考',
                ]),
            ],
        ]));

        $I->sendPost("users/{$this->examples->ltcsProjects[0]->userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['programs.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]);
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]);

        $I->sendPOST('users/userId/ltcs-projects', $ltcsProject);

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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]);
        $userId = $this->examples->users[14]->id;

        $I->sendPOST("users/{$userId}/ltcs-projects", $ltcsProject);

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
        $ltcsProject = $this->defaultParam($this->examples->ltcsProjects[0]);

        $I->sendPOST("users/{$this->examples->ltcsProjects[0]->userId}/ltcs-projects", $ltcsProject);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成
     *
     * @param \Domain\Project\LtcsProject $ltcsProject
     * @return array
     */
    private function defaultParam(LtcsProject $ltcsProject): array
    {
        return [
            'officeId' => $ltcsProject->officeId,
            'staffId' => $ltcsProject->staffId,
            'writtenOn' => $ltcsProject->writtenOn->toDateString(),
            'effectivatedOn' => $ltcsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $ltcsProject->requestFromUser,
            'requestFromFamily' => $ltcsProject->requestFromFamily,
            'problem' => $ltcsProject->problem,
            'longTermObjective' => [
                'term' => [
                    'start' => $ltcsProject->longTermObjective->term->start->toDateString(),
                    'end' => $ltcsProject->longTermObjective->term->end->toDateString(),
                ],
                'text' => $ltcsProject->longTermObjective->text,
            ],
            'shortTermObjective' => [
                'term' => [
                    'start' => $ltcsProject->shortTermObjective->term->start->toDateString(),
                    'end' => $ltcsProject->shortTermObjective->term->end->toDateString(),
                ],
                'text' => $ltcsProject->shortTermObjective->text,
            ],
            'programs' => Seq::fromArray($ltcsProject->programs)
                ->map(fn (LtcsProjectProgram $program): array => [
                    'programIndex' => $program->programIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'timeframe' => $program->timeframe->value(),
                    'amounts' => Seq::fromArray($program->amounts)
                        ->map(fn (LtcsProjectAmount $amount): array => [
                            'category' => $amount->category->value(),
                            'amount' => $amount->amount,
                        ])
                        ->toArray(),
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'serviceCode' => $program->serviceCode->toString(),
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (LtcsProjectContent $content): array => [
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
