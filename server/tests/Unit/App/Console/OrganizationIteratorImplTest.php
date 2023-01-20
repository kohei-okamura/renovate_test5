<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console;

use App\Console\OrganizationIteratorImpl;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetAllValidOrganizationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

class OrganizationIteratorImplTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetAllValidOrganizationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private OrganizationIteratorImpl $iterator;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationIteratorImplTest $self): void {
            $self->getAllValidOrganizationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();

            $self->iterator = app(OrganizationIteratorImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_iterate(): void
    {
        $this->should('use GetAllValidOrganizationUseCase', function (): void {
            $this->getAllValidOrganizationUseCase
                ->expects('handle')
                ->withNoArgs()
                ->andReturn(Seq::from($this->examples->organizations[0]));

            $f = Mockery::spy(fn () => 'RUN CALLBACK');
            $g = fn () => call_user_func($f);

            $this->iterator->iterate($g);
        });
        $this->should('call Closure', function (): void {
            $this->getAllValidOrganizationUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->organizations[0]));

            $f = Mockery::spy(fn () => 'RUN CALLBACK');
            $g = fn () => call_user_func($f);

            $this->iterator->iterate($g);
            $f->shouldHaveBeenCalled();
        });
    }
}
