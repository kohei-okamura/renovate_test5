<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsInsCardRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\LookupLtcsInsCardInteractor;

/**
 * LookupLtcsInsCardInteractor のテスト.
 */
final class LookupLtcsInsCardInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LtcsInsCardRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupLtcsInsCardInteractor $interactor;
    private LtcsInsCard $ltcsInsCard;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupLtcsInsCardInteractorTest $self): void {
            $self->ltcsInsCard = $self->examples->ltcsInsCards[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->ltcsInsCard))
                ->byDefault();
            $self->interactor = app(LookupLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsInsCards(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewLtcsInsCards(), $this->examples->users[0]->id, $this->ltcsInsCard->id);
        });
        $this->should('return a seq of LtcsInsCard', function (): void {
            $this->ltcsInsCardRepository
                ->expects('lookup')
                ->with($this->examples->ltcsInsCards[0]->id)
                ->andReturn(Seq::from($this->examples->ltcsInsCards[0]));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewLtcsInsCards(),
                $this->examples->ltcsInsCards[0]->userId,
                $this->ltcsInsCard->id
            );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->ltcsInsCard, $actual->head());
        });
        $this->should('return empty seq when userId is unmatched', function (): void {
            $this->ltcsInsCardRepository
                ->expects('lookup')
                ->with($this->ltcsInsCard->id)
                ->andReturn(Seq::from($this->ltcsInsCard));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewLtcsInsCards(),
                self::NOT_EXISTING_ID,
                $this->ltcsInsCard->id
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(0, $actual->size());
        });
    }
}
