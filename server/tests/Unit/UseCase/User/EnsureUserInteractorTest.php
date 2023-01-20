<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Test;
use UseCase\User\EnsureUserInteractor;

/**
 * EnsureUserInteractor のテスト.
 */
class EnsureUserInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use UnitSupport;

    private EnsureUserInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EnsureUserInteractorTest $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users))
                ->byDefault();

            $self->interactor = app(EnsureUserInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('succeed normally when using LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle($this->context, Permission::viewUsers(), $this->examples->users[0]->id);
        });
        $this->should('throw NotFoundException when LookupUserUseCase return empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, Permission::viewUsers(), self::NOT_EXISTING_ID);
                }
            );
        });
    }
}
