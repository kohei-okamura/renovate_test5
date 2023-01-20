<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Domain\Role\RoleRepository;
use Domain\Role\RoleScope;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;
use ScalikePHP\Seq;

/**
 * ロール Seeder.
 */
final class RoleSeeder extends Seeder
{
    private const EUSTYLELAB_ORGANIZATION_ID = 1; // Seederで入るOrganizationId
    private RoleRepository $repository;
    private TransactionManager $transaction;

    /**
     * RoleSeeder constructor.
     *
     * @param \Domain\Role\RoleRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        RoleRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->roles() as $role) {
            $this->repository->store($role);
        }
    }

    /**
     * ロールの一覧を生成する.
     *
     * @return \Domain\Role\Role[]
     */
    protected function roles(): array
    {
        $allPermissions = Seq::fromArray(Permission::all())
            ->sortBy(fn (Permission $x): string => $x->value())
            ->toArray();
        return [
            $this->generateRole([
                'id' => 1,
                'name' => 'システム管理者',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => true,
                'scope' => RoleScope::whole(), // 全体
                'sortOrder' => 1,
            ]),
            $this->generateRole([
                'id' => 2,
                'name' => '人事担当者',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::whole(),
                'sortOrder' => 2,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::viewRoles(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                ],
            ]),
            $this->generateRole([
                'id' => 3,
                'name' => '総務担当者',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::whole(),
                'sortOrder' => 3,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createOfficeGroups(),
                    Permission::deleteOfficeGroups(),
                    Permission::listOfficeGroups(),
                    Permission::updateOfficeGroups(),
                    Permission::viewOfficeGroups(),
                    Permission::createInternalOffices(),
                    Permission::deleteInternalOffices(),
                    Permission::listInternalOffices(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                ],
            ]),
            $this->generateRole([
                'id' => 4,
                'name' => 'ブロックマネージャー',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::group(),
                'sortOrder' => 4,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                    Permission::createOfficeGroups(),
                    Permission::deleteOfficeGroups(),
                    Permission::listOfficeGroups(),
                    Permission::updateOfficeGroups(),
                    Permission::viewOfficeGroups(),
                    Permission::createInternalOffices(),
                    Permission::deleteInternalOffices(),
                    Permission::listInternalOffices(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generateRole([
                'id' => 5,
                'name' => 'エリアマネージャー',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::group(),
                'sortOrder' => 5,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                    Permission::createOfficeGroups(),
                    Permission::deleteOfficeGroups(),
                    Permission::listOfficeGroups(),
                    Permission::updateOfficeGroups(),
                    Permission::viewOfficeGroups(),
                    Permission::createInternalOffices(),
                    Permission::deleteInternalOffices(),
                    Permission::listInternalOffices(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generateRole([
                'id' => 6,
                'name' => '事業所管理者',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::office(),
                'sortOrder' => 6,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                    Permission::createDwsCertifications(),
                    Permission::deleteDwsCertifications(),
                    Permission::updateDwsCertifications(),
                    Permission::viewDwsCertifications(),
                    Permission::createDwsContracts(),
                    Permission::updateDwsContracts(),
                    Permission::viewDwsContracts(),
                    Permission::createLtcsContracts(),
                    Permission::updateLtcsContracts(),
                    Permission::viewLtcsContracts(),
                    Permission::createLtcsInsCards(),
                    Permission::deleteLtcsInsCards(),
                    Permission::updateLtcsInsCards(),
                    Permission::viewLtcsInsCards(),
                    Permission::listInternalOffices(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                    Permission::createUserLtcsSubsidies(),
                    Permission::deleteUserLtcsSubsidies(),
                    Permission::updateUserLtcsSubsidies(),
                    Permission::viewUserLtcsSubsidies(),
                    Permission::updateUsersBankAccount(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generateRole([
                'id' => 7,
                'name' => 'コーディネーター',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::office(),
                'sortOrder' => 7,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generateRole([
                'id' => 8,
                'name' => '事業所事務担当',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::office(),
                'sortOrder' => 8,
                'permissions' => [ // GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                    Permission::viewRoles(),
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generateRole([
                'id' => 9,
                'name' => 'ヘルパー',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::person(),
                'sortOrder' => 9,
                'permissions' => [// GetIndexRoleCestで引っかかるので、ABC順である必要がある
                    Permission::viewStaffs(),
                ],
            ]),
            $this->generateRole([
                'id' => 10,
                'name' => '管理者',
                'isSystemAdmin' => false,
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'permissions' => $allPermissions, // 全体
                'scope' => RoleScope::whole(), // 全体
                'sortOrder' => 10,
            ]),
            $this->generateRole([
                'id' => 11,
                'name' => '契約担当者',
                'organizationId' => self::EUSTYLELAB_ORGANIZATION_ID,
                'isSystemAdmin' => false,
                'scope' => RoleScope::whole(),
                'permissions' => [
                    Permission::createDwsContracts(),
                    Permission::listDwsContracts(),
                    Permission::updateDwsContracts(),
                    Permission::viewDwsContracts(),
                    Permission::createLtcsContracts(),
                    Permission::listLtcsContracts(),
                    Permission::updateLtcsContracts(),
                    Permission::viewLtcsContracts(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
                'sortOrder' => 11,
            ]),
        ];
    }

    /**
     * インスタンスを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Role\Role
     */
    private function generateRole(array $overwrites): Role
    {
        $values = [
            'permissions' => [],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return Role::create($overwrites + $values);
    }
}
