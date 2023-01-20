<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildAuthResponseUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\GetSessionInfoInteractor;

/**
 * GetSessionInfoInteractor のテスト.
 */
final class GetSessionInfoInteractorTest extends Test
{
    use BuildAuthResponseUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private GetSessionInfoInteractor $interactor;

    private array $auth;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetSessionInfoInteractorTest $self): void {
            $self->auth = [
                'auth' => [
                    'isSystemAdmin' => false,
                    'permissions' => [],
                    'staff' => $self->examples->staffs[0],
                ],
            ];
            $self->buildAuthResponseUseCase
                ->allows('handle')
                ->andReturn($self->auth)
                ->byDefault();

            $self->interactor = app(GetSessionInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('aggregate staff permission codes', function (): void {
            $this->assertSome($this->interactor->handle($this->context), function (array $auth): void {
                $this->assertSame($this->auth, $auth);
            });
        });
        $this->should('use use case', function (): void {
            $this->buildAuthResponseUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffs[0])
                ->andReturn($this->auth);

            $this->interactor->handle($this->context);
        });
    }
}
