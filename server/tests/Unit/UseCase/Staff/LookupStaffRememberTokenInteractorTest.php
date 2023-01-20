<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRememberTokenRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Staff\LookupStaffRememberTokenInteractor;

/**
 * LookupStaffRememberTokenInteractor のテスト.
 */
final class LookupStaffRememberTokenInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffRememberTokenRepositoryMixin;
    use UnitSupport;

    /**
     * @var \Laravel\Lumen\Application|\UseCase\Staff\LookupStaffRememberTokenInteractor
     */
    private $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupStaffRememberTokenInteractorTest $self): void {
            $self->interactor = app(LookupStaffRememberTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of StaffRememberToken', function (): void {
            $this->staffRememberTokenRepository
                ->expects('lookup')
                ->with($this->examples->staffRememberTokens[0]->id)
                ->andReturn(Seq::from($this->examples->staffRememberTokens[0]));
            $actual = $this->interactor->handle($this->context, $this->examples->staffRememberTokens[0]->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->staffRememberTokens[0], $actual->head());
        });
    }
}
