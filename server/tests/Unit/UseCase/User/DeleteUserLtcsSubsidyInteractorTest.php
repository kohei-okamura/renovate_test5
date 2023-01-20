<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Closure;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserLtcsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\DeleteUserLtcsSubsidyInteractor;

/**
 * DeleteUserLtcsSubsidyInteractor のテスト
 */
class DeleteUserLtcsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserLtcsSubsidyUseCaseMixin;
    use MockeryMixin;
    use UserLtcsSubsidyRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DeleteUserLtcsSubsidyInteractor $interactor;

    /**
     * セットアップ処理
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteUserLtcsSubsidyInteractorTest $self): void {
            $self->lookupUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userLtcsSubsidies[0]))
                ->byDefault();
            $self->userLtcsSubsidyRepository
                ->allows('removeById')
                ->byDefault();
            $self->logger->allows('info')->byDefault();
            $self->interactor = app(DeleteUserLtcsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('delete subsidy after transaction begun', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::deleteUserLtcsSubsidies(),
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsSubsidies[0]->id
                )
                ->andReturn(Seq::from($this->examples->userLtcsSubsidies[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->userLtcsSubsidyRepository
                        ->expects('removeById')
                        ->with($this->examples->userLtcsSubsidies[0]->id);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->userLtcsSubsidies[0]->userId,
                $this->examples->userLtcsSubsidies[0]->id
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('公費情報が削除されました', ['id' => $this->examples->userLtcsSubsidies[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->contracts[0]->userId,
                $this->examples->contracts[0]->id
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::deleteUserLtcsSubsidies(),
                    $this->examples->users[0]->id,
                    self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->userLtcsSubsidies[0]->userId, self::NOT_EXISTING_ID);
                }
            );
        });
    }
}
