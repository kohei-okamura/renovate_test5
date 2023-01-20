<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\OwnExpenseProgramBelongsToOfficeRule} のテスト.
 */
final class OwnExpenseProgramBelongsToOfficeRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use ContextMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupOwnExpenseProgramUseCaseMixin;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (OwnExpenseProgramBelongsToOfficeRuleTest $self): void {
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateOwnExpenseProgramBelongsToOffice(): void
    {
        $this->should('pass when ownExpenseProgram is empty', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->times(0);

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => '',
                        'officeId' => $this->examples->offices[0]->id,
                    ],
                    ['value' => 'own_expense_program_belongs_to_office:officeId,' . Permission::createDwsProjects()]
                )
                    ->passes()
            );
        });
        $this->should('pass when officeId is empty', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->times(0);

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => 1,
                        'officeId' => '',
                    ],
                    ['value' => 'own_expense_program_belongs_to_office:officeId,' . Permission::createDwsProjects()]
                )
                    ->passes()
            );
        });
        $this->should('pass when lookupOwnExpenseProgramUseCase return office match', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ownExpensePrograms[0]));

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->ownExpensePrograms[0]->id,
                        'officeId' => $this->examples->offices[0]->id,
                    ],
                    ['value' => 'own_expense_program_belongs_to_office:officeId,' . Permission::createDwsProjects()]
                )
                    ->passes()
            );
        });
        $this->should('pass when lookupOwnExpenseProgramUseCase return officeId is null', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ownExpensePrograms[4]));

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->ownExpensePrograms[4]->id,
                        'officeId' => $this->examples->offices[0]->id,
                    ],
                    ['value' => 'own_expense_program_belongs_to_office:officeId,' . Permission::createDwsProjects()]
                )
                    ->passes()
            );
        });
        $this->should('fail when lookupOwnExpenseProgramUseCase return office not match', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ownExpensePrograms[2]));

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->ownExpensePrograms[2]->id,
                        'officeId' => $this->examples->offices[0]->id,
                    ],
                    ['value' => 'own_expense_program_belongs_to_office:officeId,' . Permission::createDwsProjects()]
                )
                    ->fails()
            );
        });
    }
}
