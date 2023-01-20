<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Office\EnsureOfficeInteractor;

/**
 * EnsureOfficeInteractor のテスト.
 */
class EnsureOfficeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use UnitSupport;

    private EnsureOfficeInteractor $interactor;
    private Permission $permissionArg;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EnsureOfficeInteractorTest $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->interactor = app(EnsureOfficeInteractor::class);
            $self->permissionArg = Permission::viewInternalOffices();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('succeed normally when using LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [$this->permissionArg], $this->examples->offices[0]->id)
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle($this->context, [$this->permissionArg], $this->examples->offices[0]->id);
        });
        $this->should('throw NotFoundException when LookupOfficeUseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [$this->permissionArg], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, [$this->permissionArg], self::NOT_EXISTING_ID);
                }
            );
        });
    }
}
