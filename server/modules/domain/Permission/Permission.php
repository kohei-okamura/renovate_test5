<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Permission;

use Domain\Enum;

/**
 * 権限.
 *
 * @method static Permission listUsers() 一覧参照
 * @method static Permission viewUsers() 詳細参照
 * @method static Permission createUsers() 登録
 * @method static Permission updateUsers() 編集
 * @method static Permission deleteUsers() 削除
 * @method static Permission viewUsersBankAccount() 銀行口座::参照
 * @method static Permission updateUsersBankAccount() 銀行口座::編集
 * @method static Permission listDwsContracts() 障害福祉サービス契約::一覧参照
 * @method static Permission viewDwsContracts() 障害福祉サービス契約::詳細参照
 * @method static Permission createDwsContracts() 障害福祉サービス契約::登録
 * @method static Permission updateDwsContracts() 障害福祉サービス契約::編集
 * @method static Permission deleteDwsContracts() 障害福祉サービス契約::削除
 * @method static Permission listDwsCertifications() 障害福祉サービス受給者証::一覧参照
 * @method static Permission viewDwsCertifications() 障害福祉サービス受給者証::詳細参照
 * @method static Permission createDwsCertifications() 障害福祉サービス受給者証::登録
 * @method static Permission updateDwsCertifications() 障害福祉サービス受給者証::編集
 * @method static Permission deleteDwsCertifications() 障害福祉サービス受給者証::削除
 * @method static Permission listUserDwsSubsidies() 障害福祉サービス自治体助成情報::一覧参照
 * @method static Permission viewUserDwsSubsidies() 障害福祉サービス自治体助成情報::詳細参照
 * @method static Permission createUserDwsSubsidies() 障害福祉サービス自治体助成情報::登録
 * @method static Permission updateUserDwsSubsidies() 障害福祉サービス自治体助成情報::編集
 * @method static Permission listDwsProjects() 障害福祉サービス計画::一覧参照
 * @method static Permission viewDwsProjects() 障害福祉サービス計画::詳細参照
 * @method static Permission createDwsProjects() 障害福祉サービス計画::登録
 * @method static Permission updateDwsProjects() 障害福祉サービス計画::編集
 * @method static Permission listUserDwsCalcSpecs() 障害福祉サービス算定情報::一覧参照
 * @method static Permission createUserDwsCalcSpecs() 障害福祉サービス算定情報::登録
 * @method static Permission updateUserDwsCalcSpecs() 障害福祉サービス算定情報::編集
 * @method static Permission listLtcsContracts() 介護保険サービス契約::一覧参照
 * @method static Permission viewLtcsContracts() 介護保険サービス契約::詳細参照
 * @method static Permission createLtcsContracts() 介護保険サービス契約::登録
 * @method static Permission updateLtcsContracts() 介護保険サービス契約::編集
 * @method static Permission deleteLtcsContracts() 介護保険サービス契約::削除
 * @method static Permission listLtcsInsCards() 介護保険被保険者証::一覧参照
 * @method static Permission viewLtcsInsCards() 介護保険被保険者証::詳細参照
 * @method static Permission createLtcsInsCards() 介護保険被保険者証::登録
 * @method static Permission updateLtcsInsCards() 介護保険被保険者証::編集
 * @method static Permission deleteLtcsInsCards() 介護保険被保険者証::削除
 * @method static Permission listUserLtcsSubsidies() 介護保険サービス公費情報::一覧参照
 * @method static Permission viewUserLtcsSubsidies() 介護保険サービス公費情報::詳細参照
 * @method static Permission createUserLtcsSubsidies() 介護保険サービス公費情報::登録
 * @method static Permission updateUserLtcsSubsidies() 介護保険サービス公費情報::編集
 * @method static Permission deleteUserLtcsSubsidies() 介護保険サービス公費情報::削除
 * @method static Permission listLtcsProjects() 介護保険サービス計画::一覧参照
 * @method static Permission viewLtcsProjects() 介護保険サービス計画::詳細参照
 * @method static Permission createLtcsProjects() 介護保険サービス計画::登録
 * @method static Permission updateLtcsProjects() 介護保険サービス計画::編集
 * @method static Permission deleteLtcsProjects() 介護保険サービス計画::削除
 * @method static Permission listUserLtcsCalcSpecs() 介護保険サービス算定情報::一覧参照
 * @method static Permission createUserLtcsCalcSpecs() 介護保険サービス算定情報::登録
 * @method static Permission updateUserLtcsCalcSpecs() 介護保険サービス算定情報::編集
 * @method static Permission listStaffs() 一覧参照
 * @method static Permission viewStaffs() 詳細参照
 * @method static Permission createStaffs() 登録
 * @method static Permission updateStaffs() 編集
 * @method static Permission deleteStaffs() 削除
 * @method static Permission listInternalOffices() 一覧参照（自社）
 * @method static Permission viewInternalOffices() 詳細参照（自社）
 * @method static Permission createInternalOffices() 登録（自社）
 * @method static Permission updateInternalOffices() 編集（自社）
 * @method static Permission deleteInternalOffices() 削除（自社）
 * @method static Permission listExternalOffices() 一覧参照（他社）
 * @method static Permission viewExternalOffices() 詳細参照（他社）
 * @method static Permission createExternalOffices() 登録（他社）
 * @method static Permission updateExternalOffices() 編集（他社）
 * @method static Permission deleteExternalOffices() 削除（他社）
 * @method static Permission listOfficeGroups() 一覧参照
 * @method static Permission viewOfficeGroups() 詳細参照
 * @method static Permission createOfficeGroups() 登録
 * @method static Permission updateOfficeGroups() 編集
 * @method static Permission deleteOfficeGroups() 削除
 * @method static Permission listShifts() 一覧参照
 * @method static Permission viewShifts() 詳細参照
 * @method static Permission createShifts() 登録
 * @method static Permission importShifts() 一括登録
 * @method static Permission updateShifts() 編集
 * @method static Permission deleteShifts() 削除
 * @method static Permission listAttendances() 一覧参照
 * @method static Permission viewAttendances() 詳細参照
 * @method static Permission createAttendances() 登録
 * @method static Permission updateAttendances() 編集
 * @method static Permission deleteAttendances() 削除
 * @method static Permission listDwsProvisionReports() 一覧参照
 * @method static Permission updateDwsProvisionReports() 登録・編集
 * @method static Permission listLtcsProvisionReports() 一覧参照
 * @method static Permission updateLtcsProvisionReports() 登録・編集
 * @method static Permission listBillings() 一覧参照
 * @method static Permission viewBillings() 詳細参照
 * @method static Permission createBillings() 登録
 * @method static Permission updateBillings() 編集
 * @method static Permission deleteBillings() 削除
 * @method static Permission downloadBillings() ダウンロード
 * @method static Permission listRoles() 一覧参照
 * @method static Permission viewRoles() 詳細参照
 * @method static Permission createRoles() 登録
 * @method static Permission updateRoles() 編集
 * @method static Permission deleteRoles() 削除
 * @method static Permission listOwnExpensePrograms() 一覧参照
 * @method static Permission viewOwnExpensePrograms() 詳細参照
 * @method static Permission createOwnExpensePrograms() 登録
 * @method static Permission updateOwnExpensePrograms() 編集
 * @method static Permission createOrganizationSettings() 登録
 * @method static Permission updateOrganizationSettings() 編集
 * @method static Permission viewOrganizationSettings() 詳細参照
 * @method static Permission listUserBillings() 一覧参照
 * @method static Permission viewUserBillings() 詳細参照
 * @method static Permission createUserBillings() 登録
 * @method static Permission updateUserBillings() 編集
 * @method static Permission createWithdrawalTransactions() 登録
 * @method static Permission listWithdrawalTransactions() 一覧参照
 * @method static Permission downloadWithdrawalTransactions() ダウンロード
 * @method static Permission listComprehensiveServiceScoreSheet() 一覧参照
 * @method static Permission createComprehensiveServiceScoreSheet() 登録
 * @method static Permission updateComprehensiveServiceScoreSheet() 編集
 */
final class Permission extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'listUsers' => 'users/list',
        'viewUsers' => 'users/view',
        'createUsers' => 'users/create',
        'updateUsers' => 'users/update',
        'deleteUsers' => 'users/delete',
        'viewUsersBankAccount' => 'users/bank-account/view',
        'updateUsersBankAccount' => 'users/bank-account/update',
        'listDwsContracts' => 'dws-contracts/list',
        'viewDwsContracts' => 'dws-contracts/view',
        'createDwsContracts' => 'dws-contracts/create',
        'updateDwsContracts' => 'dws-contracts/update',
        'deleteDwsContracts' => 'dws-contracts/delete',
        'listDwsCertifications' => 'dws-certifications/list',
        'viewDwsCertifications' => 'dws-certifications/view',
        'createDwsCertifications' => 'dws-certifications/create',
        'updateDwsCertifications' => 'dws-certifications/update',
        'deleteDwsCertifications' => 'dws-certifications/delete',
        'listUserDwsSubsidies' => 'user-dws-subsidies/list',
        'viewUserDwsSubsidies' => 'user-dws-subsidies/view',
        'createUserDwsSubsidies' => 'user-dws-subsidies/create',
        'updateUserDwsSubsidies' => 'user-dws-subsidies/update',
        'listDwsProjects' => 'dws-projects/list',
        'viewDwsProjects' => 'dws-projects/view',
        'createDwsProjects' => 'dws-projects/create',
        'updateDwsProjects' => 'dws-projects/update',
        'listUserDwsCalcSpecs' => 'user-dws-calc-specs/list',
        'createUserDwsCalcSpecs' => 'user-dws-calc-specs/create',
        'updateUserDwsCalcSpecs' => 'user-dws-calc-specs/update',
        'listLtcsContracts' => 'ltcs-contracts/list',
        'viewLtcsContracts' => 'ltcs-contracts/view',
        'createLtcsContracts' => 'ltcs-contracts/create',
        'updateLtcsContracts' => 'ltcs-contracts/update',
        'deleteLtcsContracts' => 'ltcs-contracts/delete',
        'listLtcsInsCards' => 'ltcs-ins-cards/list',
        'viewLtcsInsCards' => 'ltcs-ins-cards/view',
        'createLtcsInsCards' => 'ltcs-ins-cards/create',
        'updateLtcsInsCards' => 'ltcs-ins-cards/update',
        'deleteLtcsInsCards' => 'ltcs-ins-cards/delete',
        'listUserLtcsSubsidies' => 'user-ltcs-subsidies/list',
        'viewUserLtcsSubsidies' => 'user-ltcs-subsidies/view',
        'createUserLtcsSubsidies' => 'user-ltcs-subsidies/create',
        'updateUserLtcsSubsidies' => 'user-ltcs-subsidies/update',
        'deleteUserLtcsSubsidies' => 'user-ltcs-subsidies/delete',
        'listLtcsProjects' => 'ltcs-projects/list',
        'viewLtcsProjects' => 'ltcs-projects/view',
        'createLtcsProjects' => 'ltcs-projects/create',
        'updateLtcsProjects' => 'ltcs-projects/update',
        'deleteLtcsProjects' => 'ltcs-projects/delete',
        'listUserLtcsCalcSpecs' => 'user-ltcs-calc-specs/list',
        'createUserLtcsCalcSpecs' => 'user-ltcs-calc-specs/create',
        'updateUserLtcsCalcSpecs' => 'user-ltcs-calc-specs/update',
        'listStaffs' => 'staffs/list',
        'viewStaffs' => 'staffs/view',
        'createStaffs' => 'staffs/create',
        'updateStaffs' => 'staffs/update',
        'deleteStaffs' => 'staffs/delete',
        'listInternalOffices' => 'offices/list',
        'viewInternalOffices' => 'offices/view',
        'createInternalOffices' => 'offices/create',
        'updateInternalOffices' => 'offices/update',
        'deleteInternalOffices' => 'offices/delete',
        'listExternalOffices' => 'external-offices/list',
        'viewExternalOffices' => 'external-offices/view',
        'createExternalOffices' => 'external-offices/create',
        'updateExternalOffices' => 'external-offices/update',
        'deleteExternalOffices' => 'external-offices/delete',
        'listOfficeGroups' => 'office-groups/list',
        'viewOfficeGroups' => 'office-groups/view',
        'createOfficeGroups' => 'office-groups/create',
        'updateOfficeGroups' => 'office-groups/update',
        'deleteOfficeGroups' => 'office-groups/delete',
        'listShifts' => 'shifts/list',
        'viewShifts' => 'shifts/view',
        'createShifts' => 'shifts/create',
        'importShifts' => 'shifts/import',
        'updateShifts' => 'shifts/update',
        'deleteShifts' => 'shifts/delete',
        'listAttendances' => 'attendances/list',
        'viewAttendances' => 'attendances/view',
        'createAttendances' => 'attendances/create',
        'updateAttendances' => 'attendances/update',
        'deleteAttendances' => 'attendances/delete',
        'listDwsProvisionReports' => 'dws-provision-reports/list',
        'updateDwsProvisionReports' => 'dws-provision-reports/update',
        'listLtcsProvisionReports' => 'ltcs-provision-reports/list',
        'updateLtcsProvisionReports' => 'ltcs-provision-reports/update',
        'listBillings' => 'billings/list',
        'viewBillings' => 'billings/view',
        'createBillings' => 'billings/create',
        'updateBillings' => 'billings/update',
        'deleteBillings' => 'billings/delete',
        'downloadBillings' => 'billings/download',
        'listRoles' => 'roles/list',
        'viewRoles' => 'roles/view',
        'createRoles' => 'roles/create',
        'updateRoles' => 'roles/update',
        'deleteRoles' => 'roles/delete',
        'listOwnExpensePrograms' => 'own-expense-programs/list',
        'viewOwnExpensePrograms' => 'own-expense-programs/view',
        'createOwnExpensePrograms' => 'own-expense-programs/create',
        'updateOwnExpensePrograms' => 'own-expense-programs/update',
        'createOrganizationSettings' => 'organization-settings/create',
        'updateOrganizationSettings' => 'organization-settings/update',
        'viewOrganizationSettings' => 'organization-settings/view',
        'listUserBillings' => 'user-billings/list',
        'viewUserBillings' => 'user-billings/view',
        'createUserBillings' => 'user-billings/create',
        'updateUserBillings' => 'user-billings/update',
        'createWithdrawalTransactions' => 'withdrawal-transactions/create',
        'listWithdrawalTransactions' => 'withdrawal-transactions/list',
        'downloadWithdrawalTransactions' => 'withdrawal-transactions/download',
        'listComprehensiveServiceScoreSheet' => 'comprehensive-service-score-sheet/list',
        'createComprehensiveServiceScoreSheet' => 'comprehensive-service-score-sheet/create',
        'updateComprehensiveServiceScoreSheet' => 'comprehensive-service-score-sheet/update',
    ];
}
