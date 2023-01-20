<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Contract;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ContractRepositoryMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Contract\IdentifyContractInteractor;

/**
 * {@link \UseCase\Contract\IdentifyContractInteractor} Test.
 */
final class IdentifyContractInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ContractRepositoryMixin;
    use ExamplesConsumer;
    use EnsureUserUseCaseMixin;
    use EnsureOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyContractInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (IdentifyContractInteractorTest $self): void {
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->contractRepository
                ->allows('lookupByUserId')
                ->with($self->examples->contracts[0]->id)
                ->andReturn(Map::from([$self->examples->contracts[0]->id => Seq::from($self->examples->contracts[0])]))
                ->byDefault();

            $self->interactor = app(IdentifyContractInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use repository', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $serviceSegment = ServiceSegment::disabilitiesWelfare();
            $contract = $this->examples->contracts[0]->copy(['terminatedOn' => null]);
            $this->contractRepository
                ->expects('lookupByUserId')
                ->with($userId)
                ->andReturn(Map::from([$userId => Seq::from($contract)]));

            $actual = $this->interactor
                ->handle($this->context, Permission::createShifts(), $officeId, $userId, $serviceSegment, Carbon::now());
            $this->assertSome($actual, function (Contract $x) use ($contract): void {
                $this->assertSame($contract, $x);
            });
        });
        $this->should('return contract if terminatedOn less than targetDate', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[3]->id;
            $serviceSegment = ServiceSegment::disabilitiesWelfare();
            $targetDate = Carbon::parse('2022-06-01');
            $contract = $this->examples->contracts[10]->copy([
                'contractedOn' => Carbon::parse('2021-07-01'),
                'terminatedOn' => Carbon::parse('2022-07-01'),
            ]);
            $this->contractRepository
                ->expects('lookupByUserId')
                ->with($userId)
                ->andReturn(Map::from([$userId => Seq::from($contract)]));

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::createShifts(),
                    $officeId,
                    $userId,
                    $serviceSegment,
                    $targetDate
                );
            $this->assertSome($actual, function (Contract $x) use ($contract): void {
                $this->assertSame($contract, $x);
            });
        });
        $this->should('return None if terminatedOn more than targetDate', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[3]->id;
            $serviceSegment = ServiceSegment::disabilitiesWelfare();
            $targetDate = Carbon::parse('2022-07-01');
            $contract = $this->examples->contracts[10]->copy([
                'contractedOn' => Carbon::parse('2021-06-01'),
                'terminatedOn' => Carbon::parse('2022-06-01'),
            ]);
            $this->contractRepository
                ->expects('lookupByUserId')
                ->with($userId)
                ->andReturn(Map::from([$userId => Seq::from($contract)]));

            $this->assertNone(
                $this->interactor
                    ->handle(
                        $this->context,
                        Permission::createShifts(),
                        $officeId,
                        $userId,
                        $serviceSegment,
                        $targetDate
                    )
            );
        });
        $this->should('return none when contract is not found', function () {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $serviceSegment = ServiceSegment::longTermCare();

            $this->assertTrue(
                $this->interactor
                    ->handle($this->context, Permission::createShifts(), $officeId, $userId, $serviceSegment, Carbon::now())
                    ->isEmpty()
            );
        });
        $this->should('use EnsureUserUseCase', function (): void {
            $permission = Permission::createShifts();
            $user = $this->examples->users[0];
            $officeId = $this->examples->offices[0]->id;
            $serviceSegment = ServiceSegment::longTermCare();

            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, $permission, $user->id)
                ->andReturnNull();

            $this->interactor
                ->handle($this->context, Permission::createShifts(), $officeId, $user->id, $serviceSegment, Carbon::now());
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $permission = Permission::createShifts();
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $serviceSegment = ServiceSegment::longTermCare();

            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [$permission], $officeId)
                ->andReturnNull();

            $this->interactor
                ->handle($this->context, Permission::createShifts(), $officeId, $userId, $serviceSegment, Carbon::now());
        });
    }
}
