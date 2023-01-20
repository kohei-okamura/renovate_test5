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
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOrganizationSettingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationSettingRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;

/**
 * {@link \UseCase\Organization\EditOrganizationSettingInteractor} のテスト.
 */
final class EditOrganizationSettingInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use OrganizationSettingRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use LookupOrganizationSettingUseCaseMixin;

    private OrganizationSetting $organizationSetting;
    private EditOrganizationSettingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditOrganizationSettingInteractorTest $self): void {
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
            $self->lookupOrganizationSettingUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->organizationSettings[0]))
                ->byDefault();

            $self->organizationSetting = $self->examples->organizationSettings[0];
            $self->interactor = app(EditOrganizationSettingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the OrganizationSetting after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupOrganizationSettingUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::updateOrganizationSettings())
                        ->andReturn(Option::from($this->examples->organizationSettings[0]));
                    $this->organizationSettingRepository
                        ->expects('store')
                        ->andReturn($this->organizationSetting);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->payload());
        });
        $this->should('return the OrganizationSetting', function (): void {
            $this->assertModelStrictEquals(
                $this->organizationSetting,
                $this->interactor->handle($this->context, $this->payload())
            );
        });
        $this->should('use organizationSettingRepository', function (): void {
            $expect = $this->examples->organizationSettings[0]->copy($this->payload() + ['updatedAt' => Carbon::now()]);
            $this->organizationSettingRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturn($expect);

            $this->interactor->handle($this->context, $this->payload());
            $this->assertModelStrictEquals($expect, $actual);
        });
        $this->should('throw a NotFoundException when LookupOrganizationSettingUseCase return Option None', function (): void {
            $this->lookupOrganizationSettingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateOrganizationSettings())
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->payload()
                    );
                }
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業者別設定が更新されました', ['id' => $this->organizationSetting->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->payload()
            );
        });
    }

    /**
     * 更新時のペイロード.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'bankingClientCode' => $this->organizationSetting->bankingClientCode,
        ];
    }
}
