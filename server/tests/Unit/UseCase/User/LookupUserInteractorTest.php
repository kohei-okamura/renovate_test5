<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\LookupUserInteractor;

/**
 * LookupUserInteractor のテスト.
 */
final class LookupUserInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    private LookupUserInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserInteractorTest $self): void {
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->interactor = app(LookupUserInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of User', function (): void {
            $this->userRepository
                ->expects('lookup')
                ->with($this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->findContractUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), ['userIds' => [$this->examples->users[0]->id]], ['all' => true])
                ->andReturn(FinderResult::from(Seq::from($this->examples->contracts[0]), Pagination::create()));
            $this->context
                ->expects('isAccessibleTo')
                ->with(Permission::viewUsers(), $this->examples->users[0]->organizationId, equalTo([$this->examples->contracts[0]->officeId]))
                ->andReturn(true);

            $actual = $this->interactor->handle($this->context, Permission::viewUsers(), $this->examples->users[0]->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->users[0], $actual->head());
        });
        $this->should('return EmptySer when different organizationId given', function (): void {
            $user = $this->examples->users[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->userRepository
                ->allows('lookup')
                ->andReturn(Seq::from($user));
            $this->findContractUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), ['userIds' => [$this->examples->users[0]->id]], ['all' => true])
                ->andReturn(FinderResult::from(Seq::from($this->examples->contracts[0]), Pagination::create()));
            $this->context
                ->expects('isAccessibleTo')
                ->with(Permission::viewUsers(), self::NOT_EXISTING_ID, [$this->examples->contracts[0]->officeId])
                ->andReturn(false);

            $actual = $this->interactor->handle($this->context, Permission::viewUsers(), $this->examples->users[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
