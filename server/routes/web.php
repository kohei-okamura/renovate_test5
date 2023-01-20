<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CallingController;
use App\Http\Controllers\CopayListController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DummyController;
use App\Http\Controllers\DwsAreaGradeController;
use App\Http\Controllers\DwsBillingController;
use App\Http\Controllers\DwsBillingCopayCoordinationController;
use App\Http\Controllers\DwsBillingFileController;
use App\Http\Controllers\DwsBillingServiceReportController;
use App\Http\Controllers\DwsBillingStatementController;
use App\Http\Controllers\DwsCertificationController;
use App\Http\Controllers\DwsContractController;
use App\Http\Controllers\DwsProjectController;
use App\Http\Controllers\DwsProjectServiceMenuController;
use App\Http\Controllers\DwsProvisionReportController;
use App\Http\Controllers\HomeHelpServiceCalcSpecController;
use App\Http\Controllers\HomeVisitLongTermCareCalcSpecController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LtcsAreaGradeController;
use App\Http\Controllers\LtcsBillingController;
use App\Http\Controllers\LtcsBillingFileController;
use App\Http\Controllers\LtcsBillingStatementController;
use App\Http\Controllers\LtcsContractController;
use App\Http\Controllers\LtcsHomeVisitLongTermCareDictionaryEntryController;
use App\Http\Controllers\LtcsInsCardController;
use App\Http\Controllers\LtcsProjectController;
use App\Http\Controllers\LtcsProjectServiceMenuController;
use App\Http\Controllers\LtcsProvisionReportController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OfficeGroupController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\OrganizationSettingController;
use App\Http\Controllers\OwnExpenseProgramController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffBankAccountController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffPasswordResetController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserBankAccountController;
use App\Http\Controllers\UserBillingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDwsCalcSpecController;
use App\Http\Controllers\UserDwsSubsidyController;
use App\Http\Controllers\UserLtcsCalcSpecController;
use App\Http\Controllers\UserLtcsSubsidyController;
use App\Http\Controllers\VisitingCareForPwsdCalcSpecController;
use App\Http\Controllers\WithdrawalTransactionController;
use App\Http\Middleware\AuthorizeMiddleware;
use Domain\Permission\Permission;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// ---------------------------------------
// 障害福祉サービス地域区分
// ---------------------------------------
$app->router->get('/dws-area-grades', DwsAreaGradeController::class . '@getIndex');

// ---------------------------------------
// 障害福祉サービス請求
// ---------------------------------------
$app->router->post(
    '/dws-billings',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::createBillings()),
        'uses' => DwsBillingController::class . '@create',
    ]
);
$app->router->get(
    '/dws-billings/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => DwsBillingController::class . '@get',
    ]
);
$app->router->get(
    '/dws-billings/',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::listBillings()),
        'uses' => DwsBillingController::class . '@getIndex',
    ]
);
$app->router->put(
    '/dws-billings/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingController::class . '@status',
    ]
);
$app->router->post(
    '/dws-billings/{id:[1-9][0-9]*}/copy',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::createBillings()),
        'uses' => DwsBillingController::class . '@copy',
    ]
);

// ---------------------------------------
// 障害福祉サービス利用者負担上限額管理結果票
// ---------------------------------------
$app->router->post(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBundleId:[1-9][0-9]*}/copay-coordinations/',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::createBillings()),
        'uses' => DwsBillingCopayCoordinationController::class . '@create',
    ],
);
$app->router->get(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBillingBundleId:[1-9][0-9]*}/copay-coordinations/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => DwsBillingCopayCoordinationController::class . '@get',
    ],
);
$app->router->put(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBillingBundleId:[1-9][0-9]*}/copay-coordinations/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingCopayCoordinationController::class . '@update',
    ],
);
$app->router->put(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBillingBundleId:[1-9][0-9]*}/copay-coordinations/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingCopayCoordinationController::class . '@status',
    ],
);
$app->router->get(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBillingBundleId:[1-9][0-9]*}/copay-coordinations/{id:[1-9][0-9]*}.pdf',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::downloadBillings()),
        'uses' => DwsBillingCopayCoordinationController::class . '@download',
    ]
);

// ---------------------------------------
// 障害福祉サービス：サービス提供実績記録票
// ---------------------------------------
$app->router->get(
    'dws-billings/{dwsBillingId:[1-9][0-9]*}/bundles/{dwsBillingBundleId:[1-9][0-9]*}/reports/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => DwsBillingServiceReportController::class . '@get',
    ],
);
$app->router->put(
    'dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/reports/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingServiceReportController::class . '@status',
    ],
);
$app->router->post(
    'dws-billings/{billingId:[1-9][0-9]*}/service-report-status-update',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingServiceReportController::class . '@bulkStatus',
    ],
);

// ---------------------------------------
// 障害福祉サービス：明細書
// ---------------------------------------
$app->router->put(
    '/dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}/copay-coordination',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@copayCoordination',
    ]
);
$app->router->put(
    '/dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}/copay-coordination-status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@copayCoordinationStatus',
    ]
);
$app->router->get(
    '/dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => DwsBillingStatementController::class . '@get',
    ]
);
$app->router->put(
    '/dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@update',
    ]
);
$app->router->put(
    '/dws-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@status',
    ]
);
$app->router->post(
    '/dws-billings/{billingId:[1-9][0-9]*}/statement-status-update',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@bulkStatus',
    ]
);
$app->router->post(
    '/dws-billings/{billingId:[1-9][0-9]*}/statement-refresh',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => DwsBillingStatementController::class . '@refresh',
    ]
);

// ---------------------------------------
// 障害福祉サービス：請求：ファイル
// ---------------------------------------
$app->router->get(
    '/dws-billings/{dwsBillingId:[1-9][0-9]*}/files/{token}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => DwsBillingFileController::class . '@get',
    ],
);

// ---------------------------------------
// 招待
// ---------------------------------------
$app->router->post('/invitations', InvitationController::class . '@create');
$app->router->get('/invitations/{token}', InvitationController::class . '@get');

// ---------------------------------------
// 介護保険サービス請求
// ---------------------------------------
$app->router->post(
    'ltcs-billings',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::createBillings()),
        'uses' => LtcsBillingController::class . '@create',
    ]
);
$app->router->get(
    '/ltcs-billings/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => LtcsBillingController::class . '@get',
    ]
);
$app->router->get(
    '/ltcs-billings',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::listBillings()),
        'uses' => LtcsBillingController::class . '@getIndex',
    ]
);
$app->router->put(
    '/ltcs-billings/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => LtcsBillingController::class . '@status',
    ]
);

// ---------------------------------------
// 介護保険サービス：明細書
// ---------------------------------------
$app->router->get(
    '/ltcs-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => LtcsBillingStatementController::class . '@get',
    ]
);
$app->router->put(
    '/ltcs-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => LtcsBillingStatementController::class . '@update',
    ]
);
$app->router->put(
    '/ltcs-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/{id:[1-9][0-9]*}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => LtcsBillingStatementController::class . '@status',
    ]
);
$app->router->post(
    '/ltcs-billings/{billingId:[1-9][0-9]*}/bundles/{billingBundleId:[1-9][0-9]*}/statements/bulk-status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => LtcsBillingStatementController::class . '@bulkStatus',
    ]
);
$app->router->post(
    '/ltcs-billings/{billingId:[1-9][0-9]*}/statement-refresh',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateBillings()),
        'uses' => LtcsBillingStatementController::class . '@refresh',
    ]
);

// ---------------------------------------
// 介護保険サービス：請求：ファイル
// ---------------------------------------
$app->router->get(
    '/ltcs-billings/{id:[1-9][0-9]*}/files/{token}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::viewBillings()),
        'uses' => LtcsBillingFileController::class . '@get',
    ]
);

// ---------------------------------------
// ジョブ
// ---------------------------------------
$app->router->get('/jobs/{token}', JobController::class . '@get');

// ---------------------------------------
// 介保地域区分
// ---------------------------------------
$app->router->get('/ltcs-area-grades', LtcsAreaGradeController::class . '@getIndex');

// ---------------------------------------
// 介護保険サービス：予実
// ---------------------------------------
$app->router->get(
    '/ltcs-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
        'uses' => LtcsProvisionReportController::class . '@get', // 更新時に利用するAPIのため更新権限
    ]
);
$app->router->get('/ltcs-provision-reports', [
    'middleware' => AuthorizeMiddleware::with(Permission::listLtcsProvisionReports()),
    'uses' => LtcsProvisionReportController::class . '@getIndex',
]);
$app->router->put(
    '/ltcs-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
        'uses' => LtcsProvisionReportController::class . '@update',
    ]
);
$app->router->put(
    '/ltcs-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
        'uses' => LtcsProvisionReportController::class . '@status',
    ]
);
$app->router->delete(
    '/ltcs-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
        'uses' => LtcsProvisionReportController::class . '@delete',
    ]
);
$app->router->post(
    '/ltcs-provision-report-score-summary',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
        'uses' => LtcsProvisionReportController::class . '@getScoreSummary',
    ]
);
$app->router->post('/ltcs-provision-report-sheets', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
    'uses' => LtcsProvisionReportController::class . '@createSheet',
]);
$app->router->get('/ltcs-provision-reports/download/{dir}/{filename}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProvisionReports()),
    'uses' => DownloadController::class . '@download',
]);

// ---------------------------------------
// 障害福祉サービス：予実
// ---------------------------------------
$app->router->get(
    '/dws-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()), // 更新時に利用するAPIのため更新権限
        'uses' => DwsProvisionReportController::class . '@get',
    ]
);
$app->router->get('/dws-provision-reports', [
    'middleware' => AuthorizeMiddleware::with(Permission::listDwsProvisionReports()),
    'uses' => DwsProvisionReportController::class . '@getIndex',
]);
$app->router->put(
    '/dws-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
        'uses' => DwsProvisionReportController::class . '@update',
    ]
);
$app->router->put(
    '/dws-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}/status',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
        'uses' => DwsProvisionReportController::class . '@status',
    ]
);
$app->router->delete(
    '/dws-provision-reports/{officeId:[1-9][0-9]*}/{userId:[1-9][0-9]*}/{providedIn:[1-9]\d{3}-(?:0[1-9]|1[0-2])}',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
        'uses' => DwsProvisionReportController::class . '@delete',
    ]
);
$app->router->post(
    '/dws-provision-report-time-summary',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
        'uses' => DwsProvisionReportController::class . '@getTimeSummary',
    ]
);
$app->router->post(
    '/dws-service-report-previews',
    [
        'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
        'uses' => DwsProvisionReportController::class . '@createPreview',
    ]
);
$app->router->get('/dws-service-report-previews/download/{dir}/{filename}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProvisionReports()),
    'uses' => DownloadController::class . '@download',
]);

// ---------------------------------------
// 事業者別設定
// ---------------------------------------
$app->router->get('/setting', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewOrganizationSettings()),
    'uses' => OrganizationSettingController::class . '@get',
]);
$app->router->post('/setting', [
    'middleware' => AuthorizeMiddleware::with(Permission::createOrganizationSettings()),
    'uses' => OrganizationSettingController::class . '@create',
]);
$app->router->put('/setting', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateOrganizationSettings()),
    'uses' => OrganizationSettingController::class . '@update',
]);

// ---------------------------------------
// 事業所
// ---------------------------------------
$app->router->post('/offices', [
    'middleware' => AuthorizeMiddleware::with(Permission::createInternalOffices(), Permission::createExternalOffices()),
    'uses' => OfficeController::class . '@create',
]);
$app->router->get('/offices/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewInternalOffices(), Permission::viewExternalOffices()),
    'uses' => OfficeController::class . '@get',
]);
$app->router->put('/offices/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateInternalOffices(), Permission::updateExternalOffices()),
    'uses' => OfficeController::class . '@update',
]);
$app->router->get('/offices', [
    'middleware' => AuthorizeMiddleware::with(Permission::listInternalOffices(), Permission::listExternalOffices()),
    'uses' => OfficeController::class . '@getIndex',
]);

// ---------------------------------------
// 事業所算定情報（障害・居宅介護）
// ---------------------------------------
$app->router->post('/offices/{officeId:[1-9][0-9]*}/home-help-service-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createInternalOffices()),
    'uses' => HomeHelpServiceCalcSpecController::class . '@create',
]);
$app->router->put('/offices/{officeId:[1-9][0-9]*}/home-help-service-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateInternalOffices()),
    'uses' => HomeHelpServiceCalcSpecController::class . '@update',
]);
$app->router->get('/offices/{officeId:[1-9][0-9]*}/home-help-service-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewInternalOffices()),
    'uses' => HomeHelpServiceCalcSpecController::class . '@get',
]);

// ---------------------------------------
// 事業所算定情報（介保・訪問介護）
// ---------------------------------------
$app->router->post('/offices/{officeId:[1-9][0-9]*}/home-visit-long-term-care-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createInternalOffices()),
    'uses' => HomeVisitLongTermCareCalcSpecController::class . '@create',
]);
$app->router->put('/offices/{officeId:[1-9][0-9]*}/home-visit-long-term-care-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateInternalOffices()),
    'uses' => HomeVisitLongTermCareCalcSpecController::class . '@update',
]);
$app->router->get('/offices/{officeId:[1-9][0-9]*}/home-visit-long-term-care-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewInternalOffices()),
    'uses' => HomeVisitLongTermCareCalcSpecController::class . '@get',
]);
$app->router->get('/offices/{officeId:[1-9][0-9]*}/home-visit-long-term-care-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewInternalOffices()),
    'uses' => HomeVisitLongTermCareCalcSpecController::class . '@identify',
]);

// ---------------------------------------
// 事業所グループ
// ---------------------------------------
$app->router->post('/office-groups', [
    'middleware' => AuthorizeMiddleware::with(Permission::createOfficeGroups()),
    'uses' => OfficeGroupController::class . '@create',
]);
$app->router->put('/office-groups/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateOfficeGroups()),
    'uses' => OfficeGroupController::class . '@update',
]);
$app->router->put('/office-groups', OfficeGroupController::class . '@bulkUpdate');
$app->router->get('/office-groups', [
    'middleware' => AuthorizeMiddleware::with(Permission::listOfficeGroups()),
    'uses' => OfficeGroupController::class . '@getIndex',
]);
$app->router->get('/office-groups/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewOfficeGroups()),
    'uses' => OfficeGroupController::class . '@get',
]);
$app->router->delete('/office-groups/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::deleteOfficeGroups()),
    'uses' => OfficeGroupController::class . '@delete',
]);

// ---------------------------------------
// 事業所算定情報（障害・重度訪問介護）
// ---------------------------------------
$app->router->post('/offices/{officeId:[1-9][0-9]*}/visiting-care-for-pwsd-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createInternalOffices()),
    'uses' => VisitingCareForPwsdCalcSpecController::class . '@create',
]);
$app->router->put('/offices/{officeId:[1-9][0-9]*}/visiting-care-for-pwsd-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateInternalOffices()),
    'uses' => VisitingCareForPwsdCalcSpecController::class . '@update',
]);
$app->router->get('/offices/{officeId:[1-9][0-9]*}/visiting-care-for-pwsd-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewInternalOffices()),
    'uses' => VisitingCareForPwsdCalcSpecController::class . '@get',
]);

// ---------------------------------------
// 自費サービス情報
// ---------------------------------------
$app->router->post('/own-expense-programs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createOwnExpensePrograms()),
    'uses' => OwnExpenseProgramController::class . '@create',
]);
$app->router->put('/own-expense-programs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateOwnExpensePrograms()),
    'uses' => OwnExpenseProgramController::class . '@update',
]);
$app->router->get('/own-expense-programs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewOwnExpensePrograms()),
    'uses' => OwnExpenseProgramController::class . '@get',
]);
$app->router->get('/own-expense-programs', [
    'middleware' => AuthorizeMiddleware::with(Permission::listOwnExpensePrograms()),
    'uses' => OwnExpenseProgramController::class . '@getIndex',
]);

// ---------------------------------------
// 介護保険サービス：訪問介護：サービスコード辞書エントリ
// ---------------------------------------
$app->router->get(
    '/ltcs-home-visit-long-term-care-dictionary',
    LtcsHomeVisitLongTermCareDictionaryEntryController::class . '@getIndex'
);
$app->router->get(
    '/ltcs-home-visit-long-term-care-dictionary-entries/{serviceCode:[A-Z0-9]{6}}',
    LtcsHomeVisitLongTermCareDictionaryEntryController::class . '@get'
);

// ---------------------------------------
// パスワードリセット
// ---------------------------------------
$app->router->post('/password-resets', StaffPasswordResetController::class . '@create');
$app->router->get('/password-resets/{token}', StaffPasswordResetController::class . '@get');
$app->router->put('/password-resets/{token}', StaffPasswordResetController::class . '@update');

// ---------------------------------------
// 権限
// ---------------------------------------
$app->router->get('/permissions', PermissionGroupController::class . '@getIndex');

// ---------------------------------------
// 勤務シフト
// ---------------------------------------
$app->router->post('/shifts', [
    'middleware' => AuthorizeMiddleware::with(Permission::createShifts()),
    'uses' => ShiftController::class . '@create',
]);
$app->router->get('/shifts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewShifts()),
    'uses' => ShiftController::class . '@get',
]);
$app->router->put('/shifts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateShifts()),
    'uses' => ShiftController::class . '@update',
]);
$app->router->get('/shifts', [
    'middleware' => AuthorizeMiddleware::with(Permission::listShifts()),
    'uses' => ShiftController::class . '@getIndex',
]);
$app->router->post('/shift-imports', [
    'middleware' => AuthorizeMiddleware::with(Permission::importShifts()),
    'uses' => ShiftController::class . '@import',
]);
$app->router->post('/shifts/{id:[1-9][0-9]*}/cancel', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateShifts()),
    'uses' => ShiftController::class . '@cancel',
]);
$app->router->post('/shifts/cancel', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateShifts()),
    'uses' => ShiftController::class . '@bulkCancel',
]);
$app->router->post('/shift-templates', [
    'middleware' => AuthorizeMiddleware::with(Permission::createShifts()),
    'uses' => ShiftController::class . '@createTemplate',
]);
$app->router->get('/shift-templates/download/{dir}/{filename}', [
    'middleware' => AuthorizeMiddleware::with(Permission::createShifts()),
    'uses' => DownloadController::class . '@download',
]);
$app->router->post('/shifts/confirmation', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateShifts()),
    'uses' => ShiftController::class . '@confirm',
]);

// ---------------------------------------
// 勤務実績
// ---------------------------------------
$app->router->post('/attendances', [
    'middleware' => AuthorizeMiddleware::with(Permission::createAttendances()),
    'uses' => AttendanceController::class . '@create',
]);
$app->router->get('/attendances/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewAttendances()),
    'uses' => AttendanceController::class . '@get',
]);
$app->router->put('/attendances/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateAttendances()),
    'uses' => AttendanceController::class . '@update',
]);
$app->router->post('/attendances/{id:[1-9][0-9]*}/cancel', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateAttendances()),
    'uses' => AttendanceController::class . '@cancel',
]);
$app->router->post('/attendances/cancel', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateAttendances()),
    'uses' => AttendanceController::class . '@bulkCancel',
]);
$app->router->get('/attendances', [
    'middleware' => AuthorizeMiddleware::with(Permission::listAttendances()),
    'uses' => AttendanceController::class . '@getIndex',
]);
$app->router->post('/attendances/confirmation', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateAttendances()),
    'uses' => AttendanceController::class . '@confirm',
]);

// ---------------------------------------
// ロール
// ---------------------------------------
$app->router->post('/roles', [
    'middleware' => AuthorizeMiddleware::with(Permission::createRoles()),
    'uses' => RoleController::class . '@create',
]);
$app->router->get('/roles/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewRoles()),
    'uses' => RoleController::class . '@get',
]);
$app->router->put('/roles/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateRoles()),
    'uses' => RoleController::class . '@update',
]);
$app->router->get('/roles', [
    'middleware' => AuthorizeMiddleware::with(Permission::listRoles()),
    'uses' => RoleController::class . '@getIndex',
]);
$app->router->delete('roles/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::deleteRoles()),
    'uses' => RoleController::class . '@delete',
]);

// ---------------------------------------
// セッション
// ---------------------------------------
$app->router->post('/sessions', SessionController::class . '@create');
$app->router->delete('/sessions', SessionController::class . '@delete');
$app->router->get('/sessions/my', SessionController::class . '@get');

// ---------------------------------------
// スタッフ
// ---------------------------------------
$app->router->put('/staff-verifications/{token}', StaffController::class . '@verify');

$app->router->post('/staffs', StaffController::class . '@create');
$app->router->get('/staffs/distances', [
    'middleware' => AuthorizeMiddleware::with(Permission::listStaffs()),
    'uses' => StaffController::class . '@distances',
]);
$app->router->get('/staffs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewStaffs()),
    'uses' => StaffController::class . '@get',
]);
$app->router->put('/staffs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateStaffs()),
    'uses' => StaffController::class . '@update',
]);
$app->router->get('/staffs', [
    'middleware' => AuthorizeMiddleware::with(Permission::listStaffs()),
    'uses' => StaffController::class . '@getIndex',
]);

// ---------------------------------------
// スタッフ銀行口座
// ---------------------------------------
$app->router->put('/staffs/{staffId}/bank-account', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateStaffs()),
    'uses' => StaffBankAccountController::class . '@update',
]);

// ---------------------------------------
// スタッフ出勤確認
// ---------------------------------------
$app->router->post('/callings/{token}/acknowledges', CallingController::class . '@acknowledges');
$app->router->get('/callings/{token}/shifts', [
    'middleware' => AuthorizeMiddleware::with(Permission::listShifts()),
    'uses' => CallingController::class . '@shifts',
]);

// ---------------------------------------
// 選択肢
// ---------------------------------------
$app->router->get('/options/offices', OptionController::class . '@offices');
$app->router->get('/options/office-groups', OptionController::class . '@officeGroups');
$app->router->get('/options/roles', OptionController::class . '@roles');
$app->router->get('/options/staffs', OptionController::class . '@staffs');
$app->router->get('/options/users', OptionController::class . '@users');

// ---------------------------------------
// システムステータス
// ---------------------------------------
$app->router->get('/status', StatusController::class . '@get');

// ---------------------------------------
// 利用者
// ---------------------------------------
$app->router->post('/users', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUsers()),
    'uses' => UserController::class . '@create',
]);
$app->router->get('/users/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUsers()),
    'uses' => UserController::class . '@get',
]);
$app->router->put('/users/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUsers()),
    'uses' => UserController::class . '@update',
]);
$app->router->get('/users', [
    'middleware' => AuthorizeMiddleware::with(Permission::listUsers()),
    'uses' => UserController::class . '@getIndex',
]);

// ---------------------------------------
// 利用者銀行口座
// ---------------------------------------
$app->router->put('/users/{userId:[1-9][0-9]*}/bank-account', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUsersBankAccount()),
    'uses' => UserBankAccountController::class . '@update',
]);

// ---------------------------------------
// 障害福祉サービス：計画
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-projects', [
    'middleware' => AuthorizeMiddleware::with(Permission::createDwsProjects()),
    'uses' => DwsProjectController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-projects/{id:[1-9][0-9]*}/download', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewDwsProjects()),
    'uses' => DwsProjectController::class . '@download',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-projects/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewDwsProjects()),
    'uses' => DwsProjectController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-projects/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateDwsProjects()),
    'uses' => DwsProjectController::class . '@update',
]);

// ---------------------------------------
// 障害福祉サービス：計画：サービス内容
// ---------------------------------------
$app->router->get('/dws-project-service-menus', DwsProjectServiceMenuController::class . '@getIndex');

// ---------------------------------------
// 障害福祉サービス契約
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-contracts', [
    'middleware' => AuthorizeMiddleware::with(Permission::createDwsContracts()),
    'uses' => DwsContractController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-contracts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewDwsContracts()),
    'uses' => DwsContractController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-contracts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateDwsContracts()),
    'uses' => DwsContractController::class . '@update',
]);

// ---------------------------------------
// 介護保険サービス契約
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/ltcs-contracts', [
    'middleware' => AuthorizeMiddleware::with(Permission::createLtcsContracts()),
    'uses' => LtcsContractController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-contracts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewLtcsContracts()),
    'uses' => LtcsContractController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/ltcs-contracts/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsContracts()),
    'uses' => LtcsContractController::class . '@update',
]);

// ---------------------------------------
// 障害福祉サービス受給者証
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-certifications', [
    'middleware' => AuthorizeMiddleware::with(Permission::createDwsCertifications()),
    'uses' => DwsCertificationController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-certifications/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewDwsCertifications()),
    'uses' => DwsCertificationController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-certifications/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateDwsCertifications()),
    'uses' => DwsCertificationController::class . '@update',
]);
$app->router->delete('/users/{userId:[1-9][0-9]*}/dws-certifications/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::deleteDwsCertifications()),
    'uses' => DwsCertificationController::class . '@delete',
]);

// ---------------------------------------
// 障害福祉サービス：利用者別算定情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserDwsCalcSpecs()),
    'uses' => UserDwsCalcSpecController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserDwsCalcSpecs()),
    'uses' => UserDwsCalcSpecController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserDwsCalcSpecs()),
    'uses' => UserDwsCalcSpecController::class . '@update',
]);

// ---------------------------------------
// 介護保険サービス：利用者別算定情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/ltcs-calc-specs', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserLtcsCalcSpecs()),
    'uses' => UserLtcsCalcSpecController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserLtcsCalcSpecs()),
    'uses' => UserLtcsCalcSpecController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/ltcs-calc-specs/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserLtcsCalcSpecs()),
    'uses' => UserLtcsCalcSpecController::class . '@update',
]);

// ---------------------------------------
// 介護保険被保険者証
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/ltcs-ins-cards', [
    'middleware' => AuthorizeMiddleware::with(Permission::createLtcsInsCards()),
    'uses' => LtcsInsCardController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-ins-cards/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewLtcsInsCards()),
    'uses' => LtcsInsCardController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/ltcs-ins-cards/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsInsCards()),
    'uses' => LtcsInsCardController::class . '@update',
]);
$app->router->delete('/users/{userId:[1-9][0-9]*}/ltcs-ins-cards/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::deleteLtcsInsCards()),
    'uses' => LtcsInsCardController::class . '@delete',
]);

// ---------------------------------------
// 介護保険サービス：計画
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/ltcs-projects', [
    'middleware' => AuthorizeMiddleware::with(Permission::createLtcsProjects()),
    'uses' => LtcsProjectController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-projects/{id:[1-9][0-9]*}/download', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewLtcsProjects()),
    'uses' => LtcsProjectController::class . '@download',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-projects/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewLtcsProjects()),
    'uses' => LtcsProjectController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/ltcs-projects/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateLtcsProjects()),
    'uses' => LtcsProjectController::class . '@update',
]);

// ---------------------------------------
// 介護保険サービス：計画：サービス内容
// ---------------------------------------
$app->router->get('/ltcs-project-service-menus', LtcsProjectServiceMenuController::class . '@getIndex');

// ---------------------------------------
// 公費情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/ltcs-subsidies', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserLtcsSubsidies()),
    'uses' => UserLtcsSubsidyController::class . '@create',
]);
$app->router->delete('/users/{userId:[1-9][0-9]*}/ltcs-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::deleteUserLtcsSubsidies()),
    'uses' => UserLtcsSubsidyController::class . '@delete',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/ltcs-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserLtcsSubsidies()),
    'uses' => UserLtcsSubsidyController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/ltcs-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserLtcsSubsidies()),
    'uses' => UserLtcsSubsidyController::class . '@update',
]);

// ---------------------------------------
// 自治体助成情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-subsidies', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@update',
]);

// ---------------------------------------
// 自治体助成情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-subsidies', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@update',
]);

// ---------------------------------------
// 自治体助成情報
// ---------------------------------------
$app->router->post('/users/{userId:[1-9][0-9]*}/dws-subsidies', [
    'middleware' => AuthorizeMiddleware::with(Permission::createUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@create',
]);
$app->router->get('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@get',
]);
$app->router->put('/users/{userId:[1-9][0-9]*}/dws-subsidies/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserDwsSubsidies()),
    'uses' => UserDwsSubsidyController::class . '@update',
]);

// ---------------------------------------
// 利用者請求
// ---------------------------------------
$app->router->get('user-billings', [
    'middleware' => AuthorizeMiddleware::with(Permission::listUserBillings()),
    'uses' => UserBillingController::class . '@getIndex',
]);
$app->router->get('/user-billings/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => UserBillingController::class . '@get',
]);
$app->router->put('/user-billings/{id:[1-9][0-9]*}', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserBillings()),
    'uses' => UserBillingController::class . '@update',
]);
$app->router->post('user-billings/deposit-registration', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserBillings()),
    'uses' => UserBillingController::class . '@updateDeposit',
]);
$app->router->post('/user-billings/deposit-cancellation', [
    'middleware' => AuthorizeMiddleware::with(Permission::updateUserBillings()),
    'uses' => UserBillingController::class . '@deleteDeposit',
]);
$app->router->post('/user-billing-invoices', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => UserBillingController::class . '@createInvoice',
]);
$app->router->post('/user-billing-notices', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => UserBillingController::class . '@createNotice',
]);
$app->router->post('/user-billing-receipts', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => UserBillingController::class . '@createReceipt',
]);
$app->router->post('/user-billing-statements', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => UserBillingController::class . '@createStatement',
]);
$app->router->get('/user-billings/download/{dir}/{filename}', [
    'middleware' => AuthorizeMiddleware::with(Permission::viewUserBillings()),
    'uses' => DownloadController::class . '@download',
]);

// ---------------------------------------
// 口座振替データ
// ---------------------------------------
$app->router->get('/withdrawal-transactions', [
    'middleware' => AuthorizeMiddleware::with(Permission::listWithdrawalTransactions()),
    'uses' => WithdrawalTransactionController::class . '@getIndex',
]);
$app->router->post('/withdrawal-transactions', [
    'middleware' => AuthorizeMiddleware::with(Permission::createWithdrawalTransactions()),
    'uses' => WithdrawalTransactionController::class . '@create',
]);
$app->router->post('/withdrawal-transaction-files', [
    'middleware' => AuthorizeMiddleware::with(Permission::downloadWithdrawalTransactions()),
    'uses' => WithdrawalTransactionController::class . '@createFile',
]);
$app->router->post('/withdrawal-transaction-imports', [
    'middleware' => AuthorizeMiddleware::with(Permission::downloadWithdrawalTransactions()),
    'uses' => WithdrawalTransactionController::class . '@import',
]);

// ---------------------------------------
// 利用者負担額一覧表
// ---------------------------------------
$app->router->post('/dws-billings/{billingId:[1-9][0-9]*}/copay-lists', [
    'middleware' => AuthorizeMiddleware::with(Permission::downloadBillings()),
    'uses' => CopayListController::class . '@create',
]);
$app->router->get('/copay-lists/download/{dir}/{filename}', [
    'middleware' => AuthorizeMiddleware::with(Permission::downloadBillings()),
    'uses' => DownloadController::class . '@download',
]);

// ---------------------------------------
// ダミー PDF ダウンロード
// ---------------------------------------
$app->router->get('/dummies/download/{path}', [
    'uses' => DummyController::class . '@download',
]);
