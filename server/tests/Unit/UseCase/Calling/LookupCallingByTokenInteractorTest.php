<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use ScalikePHP\Option;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingRepositoryMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Test;
use UseCase\Calling\LookupCallingByTokenInteractor;

/**
 * {@link \UseCase\Calling\LookupCallingByTokenInteractor} のテスト.
 */
class LookupCallingByTokenInteractorTest extends Test
{
    use CallingRepositoryMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LookupCallingByTokenInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setupSuite(): void
    {
        static::beforeEachSpec(function (LookupCallingByTokenInteractorTest $self): void {
            $self->callingRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->examples->callings[0]))
                ->byDefault();

            $self->interactor = app(LookupCallingByTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a some of Calling', function (): void {
            $calling = $this->examples->callings[0];

            $option = $this->interactor->handle($this->context, $calling->token);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertModelStrictEquals($calling, $option->head());
        });
        $this->should('get entity via Repository', function (): void {
            $calling = $this->examples->callings[1];
            $this->callingRepository
                ->expects('lookupOptionByToken')
                ->with($calling->token)
                ->andReturn(Option::from($calling));

            $this->interactor->handle($this->context, $calling->token);
        });
        $this->should('return None when repository returns `none`', function (): void {
            $calling = $this->examples->callings[0];
            $this->callingRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::none());

            $this->assertNone($this->interactor->handle($this->context, $calling->token));
        });
    }
}
