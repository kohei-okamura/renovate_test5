<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Permission\PermissionGroup;

/**
 * PermissionGroup Example.
 *
 * @property-read \Domain\Permission\PermissionGroup[] $permissionGroups
 */
trait PermissionGroupExample
{
    /**
     * 権限の一覧を生成する.
     *
     * @return \Domain\Permission\PermissionGroup[]
     */
    protected function permissionGroups(): array
    {
        return [
            $this->generatePermissionGroup([
                'id' => 1,
                'code' => 'offices',
                'name' => '事業所',
                'displayName' => '事業所',
                'sortOrder' => 1,
                'permissions' => [
                    Permission::createExternalOffices(),
                    Permission::deleteExternalOffices(),
                    Permission::listExternalOffices(),
                    Permission::updateExternalOffices(),
                    Permission::viewExternalOffices(),
                    Permission::createInternalOffices(),
                    Permission::deleteInternalOffices(),
                    Permission::listInternalOffices(),
                    Permission::updateInternalOffices(),
                    Permission::viewInternalOffices(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 2,
                'code' => 'groups',
                'name' => '事業所グループ',
                'displayName' => '事業所グループ',
                'sortOrder' => 2,
                'permissions' => [
                    Permission::createOfficeGroups(),
                    Permission::deleteOfficeGroups(),
                    Permission::listOfficeGroups(),
                    Permission::updateOfficeGroups(),
                    Permission::viewOfficeGroups(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 3,
                'code' => 'roles',
                'name' => 'ロール',
                'displayName' => 'ロール',
                'sortOrder' => 3,
                'permissions' => [
                    Permission::createRoles(),
                    Permission::deleteRoles(),
                    Permission::listRoles(),
                    Permission::updateRoles(),
                    Permission::viewRoles(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 4,
                'code' => 'staffs',
                'name' => 'スタッフ',
                'displayName' => 'スタッフ',
                'sortOrder' => 4,
                'permissions' => [
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 5,
                'code' => 'user',
                'name' => '利用者',
                'displayName' => '利用者',
                'sortOrder' => 5,
                'permissions' => [
                    Permission::createDwsCertifications(),
                    Permission::deleteDwsCertifications(),
                    Permission::listDwsCertifications(),
                    Permission::updateDwsCertifications(),
                    Permission::viewDwsCertifications(),
                    Permission::createDwsContracts(),
                    Permission::deleteDwsContracts(),
                    Permission::listDwsContracts(),
                    Permission::updateDwsContracts(),
                    Permission::viewDwsContracts(),
                    Permission::createDwsProjects(),
                    Permission::listDwsProjects(),
                    Permission::updateDwsProjects(),
                    Permission::viewDwsProjects(),
                    Permission::createLtcsContracts(),
                    Permission::deleteLtcsContracts(),
                    Permission::listLtcsContracts(),
                    Permission::updateLtcsContracts(),
                    Permission::viewLtcsContracts(),
                    Permission::createLtcsInsCards(),
                    Permission::deleteLtcsInsCards(),
                    Permission::listLtcsInsCards(),
                    Permission::updateLtcsInsCards(),
                    Permission::viewLtcsInsCards(),
                    Permission::createLtcsProjects(),
                    Permission::deleteLtcsProjects(),
                    Permission::listLtcsProjects(),
                    Permission::updateLtcsProjects(),
                    Permission::viewLtcsProjects(),
                    Permission::createUserDwsSubsidies(),
                    Permission::listUserDwsSubsidies(),
                    Permission::updateUserDwsSubsidies(),
                    Permission::viewUserDwsSubsidies(),
                    Permission::createUserLtcsSubsidies(),
                    Permission::deleteUserLtcsSubsidies(),
                    Permission::listUserLtcsSubsidies(),
                    Permission::updateUserLtcsSubsidies(),
                    Permission::viewUserLtcsSubsidies(),
                    Permission::updateUsersBankAccount(),
                    Permission::viewUsersBankAccount(),
                    Permission::createUsers(),
                    Permission::deleteUsers(),
                    Permission::listUsers(),
                    Permission::updateUsers(),
                    Permission::viewUsers(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 6,
                'code' => 'billings',
                'name' => '請求',
                'displayName' => '請求',
                'sortOrder' => 6,
                'permissions' => [
                    Permission::createBillings(),
                    Permission::deleteBillings(),
                    Permission::downloadBillings(),
                    Permission::listBillings(),
                    Permission::updateBillings(),
                    Permission::viewBillings(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 7,
                'code' => 'shift',
                'name' => '勤務シフト',
                'displayName' => '勤務シフト',
                'sortOrder' => 7,
                'permissions' => [
                    Permission::createShifts(),
                    Permission::deleteShifts(),
                    Permission::importShifts(),
                    Permission::listShifts(),
                    Permission::updateShifts(),
                    Permission::viewShifts(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 8,
                'code' => 'attendance',
                'name' => '勤務実績',
                'displayName' => '勤務実績',
                'sortOrder' => 8,
                'permissions' => [
                    Permission::createAttendances(),
                    Permission::deleteAttendances(),
                    Permission::listAttendances(),
                    Permission::updateAttendances(),
                    Permission::viewAttendances(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 9,
                'code' => 'ownExpensePrograms',
                'name' => '自費サービス情報',
                'displayName' => '自費サービス情報',
                'sortOrder' => 9,
                'permissions' => [
                    Permission::createOwnExpensePrograms(),
                    Permission::listOwnExpensePrograms(),
                    Permission::updateOwnExpensePrograms(),
                    Permission::viewOwnExpensePrograms(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 10,
                'code' => 'userBillings',
                'name' => '利用者請求',
                'displayName' => '利用者請求',
                'sortOrder' => 10,
                'permissions' => [
                    Permission::createUserBillings(),
                    Permission::listUserBillings(),
                    Permission::updateUserBillings(),
                    Permission::viewUserBillings(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 11,
                'code' => 'organization-settings',
                'name' => '事業者別設定',
                'displayName' => '事業者別設定',
                'sortOrder' => 11,
                'permissions' => [
                    Permission::createOrganizationSettings(),
                    Permission::updateOrganizationSettings(),
                    Permission::viewOrganizationSettings(),
                ],
            ]),
            $this->generatePermissionGroup([
                'id' => 12,
                'code' => 'withdrawalTransactions',
                'name' => '口座振替',
                'displayName' => '口座振替',
                'sortOrder' => 12,
                'permissions' => [
                    Permission::createWithdrawalTransactions(),
                    Permission::downloadWithdrawalTransactions(),
                    Permission::listWithdrawalTransactions(),
                ],
            ]),
        ];
    }

    /**
     * 権限グループを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Permission\PermissionGroup
     */
    protected function generatePermissionGroup(array $overwrites): PermissionGroup
    {
        $attrs = [
            'createdAt' => Carbon::create(2019, 12, 9, 0, 0, 0),
        ];
        return PermissionGroup::create($overwrites + $attrs);
    }
}
