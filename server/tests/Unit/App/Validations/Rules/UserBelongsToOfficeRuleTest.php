<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Task;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBelongsToOfficeRule} のテスト.
 */
final class UserBelongsToOfficeRuleTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->shifts[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBelongsToOffice(): void
    {
        $this->should('pass officeId is invalid (null validation)', function (): void {
            $this->assertTrue(
                $this->buildSpecificValidator('invalid', Task::dwsVisitingCareForPwsd()->value(), Permission::createShifts()->value())
                    ->passes()
            );
        });
        $this->should('pass when task is invalid', function (): void {
            $this->assertTrue(
                $this->buildSpecificValidator('officeId', 'INVALID', Permission::createShifts()->value())
                    ->passes()
            );
        });
        $this->should('fail when task cannot resolve serviceSegment', function (): void {
            $this->assertTrue(
                $this->buildSpecificValidator('officeId', Task::other()->value(), Permission::createShifts()->value())
                    ->fails()
            );
        });
        $this->should('fail argument 3 is not Permission', function (): void {
            $this->assertFalse(
                $this->buildSpecificValidator('officeId', Task::dwsVisitingCareForPwsd()->value(), 'INVALID')
                    ->passes()
            );
        });
        $this->should('pass when IdentifyContractUseCase return some', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    Task::dwsVisitingCareForPwsd()->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));
            $this->assertTrue(
                $this->buildSpecificValidator('officeId', Task::dwsVisitingCareForPwsd()->value(), Permission::createShifts()->value())
                    ->passes()
            );
        });
        $this->should('fail when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    Task::dwsVisitingCareForPwsd()->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::none());
            $this->assertTrue(
                $this->buildSpecificValidator('officeId', Task::dwsVisitingCareForPwsd()->value(), Permission::createShifts()->value())
                    ->fails()
            );
        });
        $this->should('pass when use the rule for import shift', function (): void {
            $validator = CustomValidator::make(
                $this->context,
                [
                    'officeId' => $this->examples->offices[0]->id,
                    'shifts' => [
                        [
                            'userId' => $this->examples->users[0]->id,
                            'task' => Task::dwsVisitingCareForPwsd()->value(),
                        ],
                    ],
                ],
                ['shifts.*.userId' => 'user_belongs_to_office:officeId,shifts.*.task,' . Permission::createShifts()],
                [],
                []
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when useCase throw NotFoundException', function (): void {
            $this->identifyContractUseCase
                ->allows('handle')
                ->andThrow(NotFoundException::class);

            $this->assertTrue(
                $this->buildSpecificValidator(
                    'officeId',
                    Task::dwsVisitingCareForPwsd()->value(),
                    Permission::createShifts()->value()
                )->fails()
            );
        });
    }

    /**
     * テスト固有のValidatorを作る.
     *
     * @param \Domain\Context\Context $context
     * @param string $officeColumn
     * @param $task
     * @param string $permission
     * @return \App\Validations\CustomValidator
     */
    private function buildSpecificValidator(string $officeColumn, $task, string $permission): CustomValidator
    {
        return $this->buildCustomValidator(
            [
                'officeId' => $this->examples->offices[0]->id,
                'userId' => $this->examples->users[0]->id,
                'task' => $task,
            ],
            ['userId' => "user_belongs_to_office:{$officeColumn},task,{$permission}"],
        );
    }
}
