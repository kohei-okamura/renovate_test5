<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\User\User;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\User\GetIndexUserOptionInteractor;

/**
 * {@link \UseCase\User\GetIndexUserOptionInteractor} のテスト.
 */
class GetIndexUserOptionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetIndexUserOptionInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexUserOptionInteractorTest $self): void {
            $self->findUserUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->users, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetIndexUserOptionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of user option', function (): void {
            $expected = Seq::fromArray($this->examples->users)
                ->map(fn (User $user): array => [
                    'text' => $user->name->displayName,
                    'value' => $user->id,
                ]);
            $actual = $this->interactor->handle($this->context, Permission::listUsers(), [$this->examples->offices[0]->id]);

            $this->assertSame(
                $expected->toArray(),
                $actual->toArray()
            );
        });
        $this->should('use FindUserUseCase', function (): void {
            $this->findUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listUsers(),
                    ['officeIds' => [$this->examples->offices[0]->id]],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->users, Pagination::create()));

            $this->interactor->handle($this->context, Permission::listUsers(), [$this->examples->offices[0]->id]);
        });
    }
}
