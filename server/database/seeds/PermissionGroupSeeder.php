<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Infrastructure\Permission\PermissionGroup;
use ScalikePHP\Seq;

/**
 * 権限グループ Seeder.
 */
final class PermissionGroupSeeder extends Seeder
{
    private const USERS = 1000;
    private const STAFFS = 1200;
    private const OFFICES = 1300;
    private const OFFICE_GROUPS = 1400;
    private const SHIFTS = 1500;
    private const ATTENDANCES = 1600;
    private const DWS_PROVISION_REPORTS = 1700;
    private const LTCS_PROVISION_REPORTS = 1800;
    private const BILLINGS = 1900;
    private const USER_BILLINGS = 1910;
    private const ROLES = 2000;
    private const OWN_EXPENSE_PROGRAMS = 2100;
    private const ORGANIZATION_SETTINGS = 2200;
    private const WITHDRAWAL_TRANSACTIONS = 2300;
    private const COMPREHENSIVE_SERVICE_SCORE_SHEET = 2400;

    private DatabaseManager $db;

    /**
     * {@link \Database\Seeders\PermissionGroupSeeder} constructor.
     */
    public function __construct()
    {
        $this->db = app('db');
    }

    /**
     * Run the database seeds.
     *
     * @throws \Throwable
     * @return void
     */
    public function run(): void
    {
        $this->db->transaction(function (): void {
            foreach ($this->permissionGroups() as $id => $attrs) {
                $permissions = Arr::pull($attrs, 'permissions');
                $model = PermissionGroup::firstOrNew(
                    compact('id'),
                    [
                        'sort_order' => $id,
                        'created_at' => Carbon::now(),
                    ]
                );
                $model->fill($attrs)->save();
                $model->syncPermissions($permissions);
            }
        });
    }

    /**
     * グループ ID ごとに権限をまとめた連想配列を返す
     *
     * @return array
     */
    public function createGroupedPermission(): array
    {
        return Seq::fromArray(Permission::all())
            ->groupBy(fn (Permission $x) => $this->getPermissionGroupId($x))
            ->mapValues(fn (Seq $x): array => $x->toArray())
            ->toAssoc();
    }

    /**
     * ロールの一覧を生成する.
     *
     * @return array[]
     */
    protected function permissionGroups(): array
    {
        $permissionsById = $this->createGroupedPermission();
        return [
            self::USERS => [
                'code' => 'users',
                'name' => '利用者',
                'display_name' => '利用者',
                'permissions' => $permissionsById[self::USERS],
            ],
            self::STAFFS => [
                'code' => 'staffs',
                'name' => 'スタッフ',
                'display_name' => 'スタッフ',
                'permissions' => $permissionsById[self::STAFFS],
            ],
            self::OFFICES => [
                'code' => 'offices',
                'name' => '事業所',
                'display_name' => '事業所',
                'permissions' => $permissionsById[self::OFFICES],
            ],
            self::OFFICE_GROUPS => [
                'code' => 'office-groups',
                'name' => '事業所グループ',
                'display_name' => '事業所グループ',
                'permissions' => $permissionsById[self::OFFICE_GROUPS],
            ],
            self::SHIFTS => [
                'code' => 'shifts',
                'name' => '勤務シフト',
                'display_name' => '勤務シフト',
                'permissions' => $permissionsById[self::SHIFTS],
            ],
            self::ATTENDANCES => [
                'code' => 'attendances',
                'name' => '勤務実績',
                'display_name' => '勤務実績',
                'permissions' => $permissionsById[self::ATTENDANCES],
            ],
            self::DWS_PROVISION_REPORTS => [
                'code' => 'dws-provision-reports',
                'name' => '障害福祉サービス予実',
                'display_name' => '障害福祉サービス予実',
                'permissions' => $permissionsById[self::DWS_PROVISION_REPORTS],
            ],
            self::LTCS_PROVISION_REPORTS => [
                'code' => 'ltcs-provision-reports',
                'name' => '介護保険サービス予実',
                'display_name' => '介護保険サービス予実',
                'permissions' => $permissionsById[self::LTCS_PROVISION_REPORTS],
            ],
            self::BILLINGS => [
                'code' => 'billings',
                'name' => '請求',
                'display_name' => '請求',
                'permissions' => $permissionsById[self::BILLINGS],
            ],
            self::USER_BILLINGS => [
                'code' => 'user-billings',
                'name' => '利用者請求',
                'display_name' => '利用者請求',
                'permissions' => $permissionsById[self::USER_BILLINGS],
            ],
            self::ROLES => [
                'code' => 'roles',
                'name' => 'ロール',
                'display_name' => 'ロール',
                'permissions' => $permissionsById[self::ROLES],
            ],
            self::OWN_EXPENSE_PROGRAMS => [
                'code' => 'own-expense-programs',
                'name' => '自費サービス',
                'display_name' => '自費サービス',
                'permissions' => $permissionsById[self::OWN_EXPENSE_PROGRAMS],
            ],
            self::ORGANIZATION_SETTINGS => [
                'code' => 'organization-settings',
                'name' => '事業者別設定',
                'display_name' => '事業者別設定',
                'permissions' => $permissionsById[self::ORGANIZATION_SETTINGS],
            ],
            self::WITHDRAWAL_TRANSACTIONS => [
                'code' => 'withdrawal-transactions',
                'name' => '口座振替',
                'display_name' => '口座振替',
                'permissions' => $permissionsById[self::WITHDRAWAL_TRANSACTIONS],
            ],
            self::COMPREHENSIVE_SERVICE_SCORE_SHEET => [
                'code' => 'comprehensive-service-score-sheet',
                'name' => '単位数表（総合事業）',
                'display_name' => '単位数表（総合事業）',
                'permissions' => $permissionsById[self::COMPREHENSIVE_SERVICE_SCORE_SHEET],
            ],
        ];
    }

    /**
     * 権限の所属するグループの ID を返す
     *
     * @param \Domain\Permission\Permission $permission
     * @return int
     */
    private function getPermissionGroupId(Permission $permission): int
    {
        return match ($permission) {
            Permission::listUsers(),
            Permission::viewUsers(),
            Permission::createUsers(),
            Permission::updateUsers(),
            Permission::deleteUsers(),
            Permission::viewUsersBankAccount(),
            Permission::updateUsersBankAccount(),
            Permission::listDwsContracts(),
            Permission::viewDwsContracts(),
            Permission::createDwsContracts(),
            Permission::updateDwsContracts(),
            Permission::deleteDwsContracts(),
            Permission::listDwsCertifications(),
            Permission::viewDwsCertifications(),
            Permission::createDwsCertifications(),
            Permission::updateDwsCertifications(),
            Permission::deleteDwsCertifications(),
            Permission::listUserDwsSubsidies(),
            Permission::viewUserDwsSubsidies(),
            Permission::createUserDwsSubsidies(),
            Permission::updateUserDwsSubsidies(),
            Permission::listUserDwsCalcSpecs(),
            Permission::createUserDwsCalcSpecs(),
            Permission::updateUserDwsCalcSpecs(),
            Permission::listDwsProjects(),
            Permission::viewDwsProjects(),
            Permission::createDwsProjects(),
            Permission::updateDwsProjects(),
            Permission::listLtcsContracts(),
            Permission::viewLtcsContracts(),
            Permission::createLtcsContracts(),
            Permission::updateLtcsContracts(),
            Permission::deleteLtcsContracts(),
            Permission::listLtcsInsCards(),
            Permission::viewLtcsInsCards(),
            Permission::createLtcsInsCards(),
            Permission::updateLtcsInsCards(),
            Permission::deleteLtcsInsCards(),
            Permission::listUserLtcsSubsidies(),
            Permission::viewUserLtcsSubsidies(),
            Permission::createUserLtcsSubsidies(),
            Permission::updateUserLtcsSubsidies(),
            Permission::deleteUserLtcsSubsidies(),
            Permission::listUserLtcsCalcSpecs(),
            Permission::createUserLtcsCalcSpecs(),
            Permission::updateUserLtcsCalcSpecs(),
            Permission::listLtcsProjects(),
            Permission::viewLtcsProjects(),
            Permission::createLtcsProjects(),
            Permission::updateLtcsProjects(),
            Permission::deleteLtcsProjects() => self::USERS,
            Permission::listStaffs(),
            Permission::viewStaffs(),
            Permission::createStaffs(),
            Permission::updateStaffs(),
            Permission::deleteStaffs() => self::STAFFS,
            Permission::listInternalOffices(),
            Permission::viewInternalOffices(),
            Permission::createInternalOffices(),
            Permission::updateInternalOffices(),
            Permission::deleteInternalOffices(),
            Permission::listExternalOffices(),
            Permission::viewExternalOffices(),
            Permission::createExternalOffices(),
            Permission::updateExternalOffices(),
            Permission::deleteExternalOffices() => self::OFFICES,
            Permission::listOfficeGroups(),
            Permission::viewOfficeGroups(),
            Permission::createOfficeGroups(),
            Permission::updateOfficeGroups(),
            Permission::deleteOfficeGroups() => self::OFFICE_GROUPS,
            Permission::listShifts(),
            Permission::viewShifts(),
            Permission::createShifts(),
            Permission::importShifts(),
            Permission::updateShifts(),
            Permission::deleteShifts() => self::SHIFTS,
            Permission::listAttendances(),
            Permission::viewAttendances(),
            Permission::createAttendances(),
            Permission::updateAttendances(),
            Permission::deleteAttendances() => self::ATTENDANCES,
            Permission::listDwsProvisionReports(),
            Permission::updateDwsProvisionReports() => self::DWS_PROVISION_REPORTS,
            Permission::listLtcsProvisionReports(),
            Permission::updateLtcsProvisionReports() => self::LTCS_PROVISION_REPORTS,
            Permission::listBillings(),
            Permission::viewBillings(),
            Permission::createBillings(),
            Permission::updateBillings(),
            Permission::deleteBillings(),
            Permission::downloadBillings() => self::BILLINGS,
            Permission::listUserBillings(),
            Permission::viewUserBillings(),
            Permission::createUserBillings(),
            Permission::updateUserBillings() => self::USER_BILLINGS,
            Permission::listRoles(),
            Permission::viewRoles(),
            Permission::createRoles(),
            Permission::updateRoles(),
            Permission::deleteRoles() => self::ROLES,
            Permission::listOwnExpensePrograms(),
            Permission::viewOwnExpensePrograms(),
            Permission::createOwnExpensePrograms(),
            Permission::updateOwnExpensePrograms() => self::OWN_EXPENSE_PROGRAMS,
            Permission::createOrganizationSettings(),
            Permission::updateOrganizationSettings(),
            Permission::viewOrganizationSettings() => self::ORGANIZATION_SETTINGS,
            Permission::listWithdrawalTransactions(),
            Permission::createWithdrawalTransactions(),
            Permission::downloadWithdrawalTransactions() => self::WITHDRAWAL_TRANSACTIONS,
            Permission::listComprehensiveServiceScoreSheet(),
            Permission::createComprehensiveServiceScoreSheet(),
            Permission::updateComprehensiveServiceScoreSheet() => self::COMPREHENSIVE_SERVICE_SCORE_SHEET,
        };
    }
}
