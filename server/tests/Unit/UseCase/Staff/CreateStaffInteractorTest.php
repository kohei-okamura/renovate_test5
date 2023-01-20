<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Domain\Staff\Staff;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BankAccountRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditInvitationUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\CreateStaffInteractor;

/**
 * {@link CreateStaffInteractor} のテスト.
 */
final class CreateStaffInteractorTest extends Test
{
    use BankAccountRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use EditInvitationUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use StaffRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const BANK_ACCOUNT_ID = 1234;
    private const STAFF_ID = 4567;

    private BankAccount $bankAccount;
    private Invitation $invitation;
    private Staff $staff;

    private CreateStaffInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->bankAccount = BankAccount::create([
                'bankName' => '',
                'bankCode' => '',
                'bankBranchName' => '',
                'bankBranchCode' => '',
                'bankAccountType' => BankAccountType::unknown(),
                'bankAccountNumber' => '',
                'bankAccountHolder' => '',
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $self->invitation = $self->examples->invitations[0];
            $self->staff = $self->examples->staffs[0]->copy([
                'bankAccountId' => self::BANK_ACCOUNT_ID,
            ]);

            $self->bankAccountRepository
                ->allows('store')
                ->andReturnUsing(fn (BankAccount $x): BankAccount => $x->copy(['id' => self::BANK_ACCOUNT_ID]))
                ->byDefault();

            $self->staffRepository
                ->allows('store')
                ->andReturnUsing(fn (Staff $x): Staff => $x->copy(['id' => self::STAFF_ID]))
                ->byDefault();

            $self->editInvitationUseCase
                ->allows('handle')
                ->andReturn($self->invitation)
                ->byDefault();

            $self->context
                ->expects('logContext')
                ->andReturn(['organizationId' => $self->examples->organizations[0]->id])
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateStaffInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in a transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn($this->staff);
            $this->bankAccountRepository->expects('store')->never();
            $this->staffRepository->expects('store')->never();
            $this->editInvitationUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->staff, Option::some($this->invitation));
        });
        $this->should('create a new bank account', function (): void {
            $this->bankAccountRepository
                ->expects('store')
                ->withArgs(fn (BankAccount $x): bool => $x->equals($this->bankAccount))
                ->andReturnUsing(fn (BankAccount $x): BankAccount => $x->copy(['id' => self::BANK_ACCOUNT_ID]));

            $this->interactor->handle($this->context, $this->staff, Option::some($this->invitation));
        });
        $this->should('store the Staff', function (): void {
            $this->staffRepository
                ->expects('store')
                ->withArgs(fn (Staff $x): bool => $x->equals($this->staff))
                ->andReturnUsing(fn (Staff $x): Staff => $x->copy(['id' => self::STAFF_ID]));

            $this->interactor->handle($this->context, $this->staff, Option::some($this->invitation));
        });
        $this->should('update the invitation when it is given', function (): void {
            $this->editInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->id, ['staffId' => self::STAFF_ID])
                ->andReturn($this->invitation);

            $this->interactor->handle($this->context, $this->staff, Option::some($this->invitation));
        });
        $this->should('update the invitation when it is not given', function (): void {
            $this->editInvitationUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->staff, Option::none());
        });
        $this->should('log', function (): void {
            $logContext = [
                'id' => self::STAFF_ID,
                'organizationId' => $this->examples->organizations[0]->id,
            ];
            $this->logger->expects('info')->with('スタッフが登録されました', $logContext);

            $this->interactor->handle($this->context, $this->staff, Option::none());
        });
    }
}
