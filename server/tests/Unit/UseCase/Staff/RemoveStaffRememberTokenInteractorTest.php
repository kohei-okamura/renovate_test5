<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRememberTokenRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\RemoveStaffRememberTokenInteractor;

/**
 * RemoveStaffRememberTokenInteractor のテスト.
 */
final class RemoveStaffRememberTokenInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use StaffRememberTokenRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /**
     * @var \Laravel\Lumen\Application|\UseCase\Staff\RemoveStaffRememberTokenInteractor
     */
    private $interactor;

    /** {@inheritdoc} */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (RemoveStaffRememberTokenInteractorTest $self): void {
            $self->staffRememberTokenRepository
                ->allows('removeById')
                ->byDefault();
            $self->logger->allows('info')->byDefault();
            $self->interactor = app(RemoveStaffRememberTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('remove StaffRememberToken', function (): void {
            $this->staffRememberTokenRepository->expects('removeById')->with($this->examples->staffRememberTokens[0]->id);
            $this->interactor->handle($this->context, $this->examples->staffRememberTokens[0]->id);
        });
        $this->should('log using info', function (): void {
            $context = [
                'id' => $this->examples->staffRememberTokens[0]->id,
                'organizationId' => $this->examples->organizations[0]->id,
            ];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフリメンバートークンが削除されました', $context);

            $this->interactor->handle($this->context, $this->examples->staffRememberTokens[0]->id);
        });
    }
}
