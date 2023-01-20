<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

return [
    'calling' => [
        'lifetime_minutes' => env('ZINGER_CALLING_LIFETIME_MINUTES', 120),
    ],
    'file' => [
        'storage' => env('ZINGER_FILE_STORAGE', 'cloud'),
        'readonly_storage' => env('ZINGER_FILE_READONLY_STORAGE', 'readonly_cloud'),
    ],
    'filename' => [
        'copay_list_pdf' => env(
            'ZINGER_FILENAME_COPAY_LIST_PDF',
            '利用者負担額一覧表_#{office}_#{providedIn}.pdf'
        ),
        'copay_list_divided_pdf' => env(
            'ZINGER_FILENAME_COPAY_LIST_DIVIDED_PDF',
            '利用者負担額一覧表_#{office}_#{providedIn}_#{toOffice}.pdf'
        ),
        'copay_list_zip' => env(
            'ZINGER_FILENAME_COPAY_LIST_ZIP',
            '利用者負担額一覧表_#{office}_#{providedIn}.zip'
        ),
        'dws_copay_coordination_csv' => env(
            'ZINGER_FILENAME_DWS_COPAY_COORDINATION_CSV',
            '利用者負担上限額管理結果票_#{office}_#{providedIn}.csv'
        ),
        'dws_copay_coordination_pdf' => env(
            'ZINGER_FILENAME_DWS_COPAY_COORDINATION_PDF',
            '利用者負担上限額管理結果票_#{office}_#{providedIn}.pdf'
        ),
        'dws_invoice_csv' => env(
            'ZINGER_FILENAME_DWS_INVOICE_CSV',
            '介護給付費等請求書・明細書_#{office}_#{providedIn}.csv'
        ),
        'dws_invoice_pdf' => env(
            'ZINGER_FILENAME_DWS_INVOICE_PDF',
            '介護給付費等請求書・明細書_#{office}_#{providedIn}.pdf'
        ),
        'dws_project_pdf' => env(
            'ZINGER_FILENAME_DWS_PROJECT_PDF',
            '居宅介護計画書_%Y%m%d%H%M%S.xlsx'
        ),
        'dws_service_report_csv' => env(
            'ZINGER_FILENAME_DWS_SERVICE_REPORT_CSV',
            'サービス提供実績記録票_#{office}_#{providedIn}.csv'
        ),
        'dws_service_report_preview_pdf' => env(
            'ZINGER_FILENAME_DWS_SERVICE_REPORT_PREVIEW_PDF',
            'サービス提供実績記録票_#{user}_#{providedIn}.pdf'
        ),
        'dws_service_report_pdf' => env(
            'ZINGER_FILENAME_DWS_SERVICE_REPORT_PDF',
            'サービス提供実績記録票_#{office}_#{providedIn}.pdf'
        ),
        'ltcs_invoice_csv' => env(
            'ZINGER_FILENAME_LTCS_INVOICE_CSV',
            '介護給付費請求書・明細書_#{office}_#{providedIn}.csv'
        ),
        'ltcs_invoice_pdf' => env(
            'ZINGER_FILENAME_LTCS_INVOICE_PDF',
            '介護給付費請求書・明細書_#{office}_#{providedIn}.pdf'
        ),
        'ltcs_project_pdf' => env(
            'ZINGER_FILENAME_LTCS_PROJECT_PDF',
            '訪問介護計画書_%Y%m%d%H%M%S.xlsx'
        ),
        'ltcs_provision_report_sheet_pdf' => env(
            'ZINGER_FILENAME_LTCS_PROVISION_REPORT_SHEET_PDF',
            'サービス提供票_#{user}_#{providedIn}.pdf'
        ),
        'shift_template' => env(
            'ZINGER_FILENAME_SHIFT_TEMPLATE',
            '予定雛形_%Y%m%d%H%M%S.xlsx'
        ),
        'user_billing_invoice_pdf' => env(
            'ZINGER_FILENAME_USER_BILLING_INVOICE_PDF',
            '請求書_%Y%m%d%H%M%S.pdf'
        ),
        'user_billing_notice_pdf' => env(
            'ZINGER_FILENAME_USER_BILLING_NOTICE_PDF',
            '代理受領額通知書_%Y%m%d%H%M%S.pdf'
        ),
        'user_billing_receipt_pdf' => env(
            'ZINGER_FILENAME_USER_BILLING_RECEIPT_PDF',
            '領収書_%Y%m%d%H%M%S.pdf'
        ),
        'user_billing_statement_pdf' => env(
            'ZINGER_FILENAME_USER_BILLING_STATEMENT_PDF',
            '介護サービス利用明細書_%Y%m%d%H%M%S.pdf'
        ),
        'withdrawal_transaction_file' => env(
            'ZINGER_FILENAME_WITHDRAWAL_TRANSACTION_FILE',
            'zengin_%Y%m%d%H%M%S.txt'
        ),
    ],
    'google' => [
        'geocoding_api_key' => env('ZINGER_GOOGLE_GEOCODING_API_KEY'),
    ],
    'host' => env('ZINGER_HOST', '%s.dev.careid.net'),
    'invitation' => [
        'lifetime_minutes' => env('ZINGER_INVITATION_LIFETIME_MINUTES', 1440),
    ],
    'password_reset' => [
        'lifetime_minutes' => env('ZINGER_PASSWORD_RESET_LIFETIME_MINUTES', 1440),
    ],
    'path' => [
        'resources' => [
            'spreadsheets' => resource_path('spreadsheets'),
        ],
        'temp' => sys_get_temp_dir(),
    ],
    'remember_token' => [
        'cookie_name' => env('ZINGER_REMEMBER_TOKEN_COOKIE_NAME', 'rememberToken'),
        'lifetime_days' => env('ZINGER_REMEMBER_TOKEN_LIFETIME_DAYS', 30),
    ],
    'service_code_api' => [
        'dws_11_url' => env('ZINGER_SERVICE_CODE_API_DWS_11_URL', 'https://service-code.staging.careid.net/dws/11'),
        'dws_12_url' => env('ZINGER_SERVICE_CODE_API_DWS_12_URL', 'https://service-code.staging.careid.net/dws/12'),
        'ltcs_url' => env('ZINGER_SERVICE_CODE_API_LTCS_URL', 'https://service-code.staging.careid.net/ltcs/11'),
    ],
    'staff_attendance_confirmation' => [
        'third' => [
            'audio_uri' => env('STAFF_ATTENDANCE_CONFIRMATION_THIRD_AUDIO_URI'),
        ],
        'fourth' => [
            'audio_uri' => env('STAFF_ATTENDANCE_CONFIRMATION_FOURTH_AUDIO_URI'),
        ],
    ],
    'twilio' => [
        'account_sid' => env('ZINGER_TWILIO_ACCOUNT_SID'),
        'api_key_secret' => env('ZINGER_TWILIO_API_KEY_SECRET'),
        'api_key_sid' => env('ZINGER_TWILIO_API_KEY_SID'),
        'from_sms_number' => env('ZINGER_TWILIO_FROM_SMS_NUMBER'),
        'from_tel_number' => env('ZINGER_TWILIO_FROM_TEL_NUMBER'),
        'token' => env('ZINGER_TWILIO_TOKEN'),
    ],
    'uri' => [
        'app_domain' => env('ZINGER_URI_APP_DOMAIN', 'zinger.test'),
        'base_path' => env('ZINGER_URI_BASE_PATH', 'api'),
        'scheme' => env('ZINGER_URI_SCHEME', 'https'),
    ],
    'url_shortener' => [
        'url' => env('ZINGER_URL_SHORTENER_URL', 'https://esl.care/'),
        'api_key' => env('ZINGER_URL_SHORTENER_KEY'),
    ],
];
