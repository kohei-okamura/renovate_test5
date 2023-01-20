<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\LookupUserBillingInteractor;

/**
 * {@link \UseCase\UserBilling\LookupUserBillingInteractor} Test.
 */
class LookupUserBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use UserBillingRepositoryMixin;

    private LookupUserBillingInteractor $interactor;
    private UserBilling $userBilling;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupUserBillingInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0]);
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->userBillingRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->userBilling = $self->examples->userBillings[0];
            $self->interactor = app(LookupUserBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of UserBilling', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewUserBillings(),
                    $this->userBilling->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->userBilling,
                $actual->head()
            );
        });
        $this->should('return empty seq when accessibleTo of Context return false', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->andReturn(false);

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewUserBillings(),
                    $this->userBilling->id
                );
            $this->assertCount(0, $actual);
        });
    }
}
