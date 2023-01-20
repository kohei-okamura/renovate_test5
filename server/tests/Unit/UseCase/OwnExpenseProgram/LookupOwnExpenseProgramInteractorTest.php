<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\OwnExpenseProgram;

use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OwnExpenseProgramRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramInteractor;

/**
 * {@link \UseCase\OwnExpenseProgram\LookupOwnExpenseProgramInteractor} のテスト.
 */
final class LookupOwnExpenseProgramInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OwnExpenseProgramRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private OwnExpenseProgram $ownExpenseProgram;
    private LookupOwnExpenseProgramInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupOwnExpenseProgramInteractorTest $self): void {
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->ownExpenseProgramRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();

            $self->ownExpenseProgram = $self->examples->ownExpensePrograms[0];
            $self->interactor = app(LookupOwnExpenseProgramInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of ownExpenseProgram', function (): void {
            $this->ownExpenseProgramRepository
                ->expects('lookup')
                ->with($this->ownExpenseProgram->id)
                ->andReturn(Seq::from($this->ownExpenseProgram));

            $actual = $this->interactor->handle($this->context, Permission::viewOwnExpensePrograms(), $this->ownExpenseProgram->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->ownExpenseProgram, $actual->head());
        });
        $this->should('use isAccessibleTo Method in Context', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->with(Permission::viewOwnExpensePrograms(), $this->examples->organizations[0]->id, [$this->ownExpenseProgram->officeId])
                ->andReturn(true);

            $this->interactor
                ->handle($this->context, Permission::viewOwnExpensePrograms(), $this->ownExpenseProgram->id);
        });

        $this->should('return empty seq when isAccessibleTo return false', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->andReturn(false);
            $actual = $this->interactor->handle($this->context, Permission::viewOwnExpensePrograms(), $this->ownExpenseProgram->id);
            $this->assertCount(0, $actual);
        });

        $this->should('return a seq if the service is for all offices even if isAccessibleTo return false', function (): void {
            $program = $this->ownExpenseProgram->copy([
                'officeId' => null,
            ]);
            $this->ownExpenseProgramRepository
                ->expects('lookup')
                ->with($program->id)
                ->andReturn(Seq::from($program));
            $this->context
                ->allows('isAccessibleTo')
                ->andReturn(false);

            $actual = $this->interactor->handle($this->context, Permission::viewOwnExpensePrograms(), $program->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($program, $actual->head());
        });

        $this->should('return empty seq if the service is for all offices but a different organization', function (): void {
            $program = $this->ownExpenseProgram->copy([
                'organizationId' => $this->context->organization->id + 10,
                'officeId' => null,
            ]);
            $this->ownExpenseProgramRepository
                ->expects('lookup')
                ->with($program->id)
                ->andReturn(Seq::from($program));

            $actual = $this->interactor->handle($this->context, Permission::viewOwnExpensePrograms(), $this->ownExpenseProgram->id);
            $this->assertCount(0, $actual);
        });
    }
}
