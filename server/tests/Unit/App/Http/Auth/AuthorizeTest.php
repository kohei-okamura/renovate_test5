<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Auth;

use App\Http\Auth\Authorize;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

class AuthorizeTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupRoleUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    /**
     * @var \App\Http\Auth\Authorize
     */
    private Authorize $authorize;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AuthorizeTest $self): void {
            $self->context
                ->expects('isAuthorizedTo')
                ->andReturn(true)
                ->byDefault();

            $self->authorize = app(Authorize::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return void if the staff is system admin', function (): void {
            $this->assertNull(
                app()->call(
                    [$this->authorize, 'handle'],
                    ['context' => $this->context, 'requiredPermission' => 'staffs/list']
                )
            );
        });
        $this->should(
            'return void if the staff has a permission of requested action method',
            function (): void {
                $this->assertNull(
                    app()->call(
                        [$this->authorize, 'handle'],
                        ['context' => $this->context, 'requiredPermission' => 'staffs/list']
                    )
                );
            }
        );
        $this->should(
            'throw AuthorizationException if the staff does not have a permission of requested action method',
            function (): void {
                $this->context
                    ->expects('isAuthorizedTo')
                    ->andReturn(false);

                $this->assertThrows(
                    AuthorizationException::class,
                    function (): void {
                        app()->call(
                            [$this->authorize, 'handle'],
                            ['context' => $this->context, 'requiredPermission' => 'offices/list']
                        );
                    }
                );
            }
        );
    }
}
