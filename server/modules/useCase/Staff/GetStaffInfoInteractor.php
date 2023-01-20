<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Lib\Exceptions\NotFoundException;
use UseCase\BankAccount\LookupBankAccountUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\Role\LookupRoleUseCase;

/**
 * スタッフ情報取得実装.
 */
final class GetStaffInfoInteractor implements GetStaffInfoUseCase
{
    private LookupBankAccountUseCase $lookupBankAccountUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private LookupRoleUseCase $lookupRoleUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\BankAccount\LookupBankAccountUseCase $lookupBankAccountUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\Role\LookupRoleUseCase $lookupRoleUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     */
    public function __construct(LookupBankAccountUseCase $lookupBankAccountUseCase, LookupOfficeUseCase $lookupOfficeUseCase, LookupRoleUseCase $lookupRoleUseCase, LookupStaffUseCase $lookupStaffUseCase)
    {
        $this->lookupBankAccountUseCase = $lookupBankAccountUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->lookupRoleUseCase = $lookupRoleUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): array
    {
        $staff = $this->lookupStaffUseCase
            ->handle($context, Permission::viewStaffs(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Staff({$id}) not found");
            });
        assert($staff instanceof Staff);
        $bankAccount = $this->lookupBankAccountUseCase->handle($context, $staff->bankAccountId)->headOption()->getOrElse(function () use ($staff): void {
            throw new NotFoundException("bankAccount with id {$staff->bankAccountId} not found");
        });
        $offices = $this->lookupOfficeUseCase->handle($context, [Permission::viewStaffs()], ...$staff->officeIds);
        $roles = $this->lookupRoleUseCase->handle($context, ...$staff->roleIds);
        return compact('bankAccount', 'offices', 'roles', 'staff');
    }
}
