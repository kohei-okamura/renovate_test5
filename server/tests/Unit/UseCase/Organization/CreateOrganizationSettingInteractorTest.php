<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Organization\OrganizationSetting;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationSettingRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;

/**
 * {@link \UseCase\Organization\CreateOrganizationSettingInteractor} のテスト.
 */
final class CreateOrganizationSettingInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use OrganizationSettingRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private OrganizationSetting $organizationSetting;
    private CreateOrganizationSettingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOrganizationSettingInteractorTest $self): void {
            $self->organizationSettingRepository
                ->allows('store')
                ->andReturn($self->examples->organizationSettings[0])
                ->byDefault();
            $self->organizationSettingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->organizationSetting = $self->examples->organizationSettings[0];
            $self->interactor = app(CreateOrganizationSettingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the organizationSettings after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->organizationSettingRepository
                        ->expects('store')
                        ->with(equalTo($this->organizationSetting->copy([
                            'organizationId' => $this->context->organization->id,
                            'createdAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->organizationSetting);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->organizationSetting);
        });
        $this->should('return the OrganizationSetting', function (): void {
            $this->assertModelStrictEquals(
                $this->organizationSetting,
                $this->interactor->handle($this->context, $this->organizationSetting)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業者別設定が登録されました', ['id' => $this->organizationSetting->id] + $context);

            $this->interactor->handle($this->context, $this->organizationSetting);
        });
    }
}
