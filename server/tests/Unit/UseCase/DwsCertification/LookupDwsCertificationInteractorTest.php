<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsCertification;

use Domain\DwsCertification\DwsCertification;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsCertificationRepositoryMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\DwsCertification\LookupDwsCertificationInteractor;

/**
 * LookupDwsCertificationInteractor のテスト.
 */
final class LookupDwsCertificationInteractorTest extends Test
{
    use ContextMixin;
    use DwsCertificationRepositoryMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupDwsCertificationInteractor $interactor;
    private DwsCertification $dwsCertification;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsCertificationInteractorTest $self): void {
            $self->dwsCertification = $self->examples->dwsCertifications[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->dwsCertificationRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsCertification))
                ->byDefault();

            $self->interactor = app(LookupDwsCertificationInteractor::class);
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
                ->with($this->context, Permission::viewDwsCertifications(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, Permission::viewDwsCertifications(), $this->examples->users[0]->id, $this->dwsCertification->id);
        });
        $this->should('return a seq of DwsCertification', function (): void {
            $this->dwsCertificationRepository
                ->expects('lookup')
                ->with($this->dwsCertification->id)
                ->andReturn(Seq::from($this->dwsCertification));

            $actual = $this->interactor->handle($this->context, Permission::viewDwsCertifications(), $this->dwsCertification->userId, $this->dwsCertification->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->dwsCertification, $actual->head());
        });
        $this->should('return empty seq when different userId given', function (): void {
            $this->dwsCertificationRepository
                ->expects('lookup')
                ->andReturn(Seq::from($this->dwsCertification));

            $actual = $this->interactor->handle($this->context, Permission::viewDwsCertifications(), self::NOT_EXISTING_ID, $this->dwsCertification->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(0, $actual->size());
        });
    }
}
