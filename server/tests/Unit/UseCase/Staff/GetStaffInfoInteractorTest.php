<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\LookupBankAccountUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Test;
use UseCase\Staff\GetStaffInfoInteractor;

/**
 * GetStaffInfoInteractor のテスト.
 */
class GetStaffInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use LookupBankAccountUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use RequestMixin;
    use UnitSupport;

    private GetStaffInfoInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetStaffInfoInteractorTest $self): void {
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupBankAccountUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->bankAccounts[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->examples->offices))
                ->byDefault();
            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->examples->roles))
                ->byDefault();

            $self->interactor = app(GetStaffInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call handle of lookupStaffUseCase', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), $this->examples->staffs[0]->id)
                ->andReturn(Seq::from($this->examples->staffs[0]));

            $this->interactor->handle($this->context, $this->examples->staffs[0]->id);
        });
        $this->should('return an array contains bankAccount', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->staffs[0]->id);

            $this->assertModelStrictEquals($this->examples->bankAccounts[0], $actual['bankAccount']);
        });
        $this->should('return an array contains offices', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->staffs[0]->id);

            $this->assertCount(count($this->examples->offices), $actual['offices']);
            foreach ($this->examples->offices as $index => $office) {
                $this->assertModelStrictEquals($office, $actual['offices'][$index]);
            }
        });
        $this->should('return an array contains roles', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->staffs[0]->id);

            $this->assertCount(count($this->examples->roles), $actual['roles']);
            foreach ($this->examples->roles as $index => $role) {
                $this->assertModelStrictEquals($role, $actual['roles'][$index]);
            }
        });
        $this->should('return an array contains staff', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->staffs[0]->id);

            $this->assertModelStrictEquals($this->examples->staffs[0], $actual['staff']);
        });
        $this->should('lookup bankAccount', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffs[0]->bankAccountId)
                ->andReturn(Seq::from($this->examples->bankAccounts[0]));
            $this->interactor->handle($this->context, $this->examples->staffs[0]->id);
        });
        $this->should('lookup offices', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewStaffs()], ...$this->examples->staffs[0]->officeIds)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->interactor->handle($this->context, $this->examples->staffs[0]->id);
        });
        $this->should('lookup roles', function (): void {
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, ...$this->examples->staffs[0]->roleIds)
                ->andReturn(Seq::from($this->examples->roles[0]));
            $this->interactor->handle($this->context, $this->examples->staffs[0]->id);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID);
                }
            );
        });
        $this->should('throw a NotFoundException when the bankAccountId not exists in db', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->staffs[0]->id);
                }
            );
        });
    }
}
