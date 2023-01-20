<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Contract;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ContractRepositoryMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Contract\LookupContractInteractor;

/**
 * LookupContractInteractor のテスト.
 */
class LookupContractInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use ContractRepositoryMixin;
    use UnitSupport;

    private LookupContractInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupContractInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupContractInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of contract', function (): void {
            $this->contractRepository
                ->expects('lookup')
                ->with($this->examples->contracts[0]->id)
                ->andReturn(Seq::from($this->examples->contracts[0]));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewDwsContracts(),
                $this->examples->users[0]->id,
                $this->examples->contracts[0]->id
            );
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->contracts[0], $actual->head());
        });

        $this->should('return empty seq when different organizationId given', function (): void {
            $contract = $this->examples->contracts[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->contractRepository
                ->allows('lookup')
                ->andReturn(Seq::from($contract));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::updateDwsContracts(),
                $this->examples->users[0]->id,
                $this->examples->contracts[0]->id
            );
            $this->assertCount(0, $actual);
        });

        $this->should('use EnsureUserUseCase', function (): void {
            $this->contractRepository
                ->expects('lookup')
                ->with($this->examples->contracts[0]->id)
                ->andReturn(Seq::from($this->examples->contracts[0]));
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsContracts(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                Permission::updateLtcsContracts(),
                $this->examples->users[0]->id,
                $this->examples->contracts[0]->id
            );
        });
    }
}
