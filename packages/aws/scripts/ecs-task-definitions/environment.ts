/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { EnvironmentVariables } from 'aws-sdk/clients/ecs'
import { EnvironmentName } from '~aws/scripts/ecs-task-definitions/environment-name'
import { Environment } from '~aws/scripts/ecs-task-definitions/functions'
import {
  region,
  ZINGER_ACCOUNT,
  ZINGER_QUEUE_NAME,
  ZINGER_SANDBOX_ACCOUNT,
  ZINGER_STAGING_ACCOUNT
} from '~aws/variables'

type EnvironmentDef = Partial<Record<EnvironmentName, string>>

const toVariables = (def: EnvironmentDef): EnvironmentVariables => Object.entries(def).map(([name, value]) => ({
  name,
  value
}))

export const environments: Record<Environment, EnvironmentVariables> = {
  prod: toVariables({
    APP_ENV: 'production',
    MAIL_DRIVER: 'sendgrid',
    MAIL_FROM_ADDRESS: 'noreplay@careid.jp',
    MAIL_FROM_NAME: '訪問介護業務支援ツール careid',
    QUEUE_CONNECTION: 'redis',
    REDIS_SCHEME: 'tls',
    SQS_KEY: '',
    SQS_PREFIX: `https://sqs.ap-northeast-1.amazonaws.com/${ZINGER_ACCOUNT}`,
    SQS_QUEUE: ZINGER_QUEUE_NAME,
    SQS_REGION: region,
    SQS_SECRET: '',
    ZINGER_HOST: '%s.careid.jp',
    ZINGER_INVITATION_LIFETIME_MINUTES: '10080',
    ZINGER_SERVICE_CODE_API_DWS_11_URL: 'https://service-code.careid.jp/dws/11',
    ZINGER_SERVICE_CODE_API_DWS_12_URL: 'https://service-code.careid.jp/dws/12',
    ZINGER_SERVICE_CODE_API_LTCS_URL: 'https://service-code.careid.jp/ltcs/11',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_FOURTH_AUDIO_URI: '__UNDEFINED__',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_THIRD_AUDIO_URI: '__UNDEFINED__',
    ZINGER_URI_APP_DOMAIN: 'careid.jp'
  }),
  staging: toVariables({
    APP_DEBUG: 'true',
    APP_ENV: 'staging',
    MAIL_DRIVER: 'sendgrid',
    MAIL_FROM_ADDRESS: 'noreplay@careid.net',
    MAIL_FROM_NAME: '[ステージング] 訪問介護業務支援ツール careid',
    QUEUE_CONNECTION: 'redis',
    REDIS_SCHEME: 'tls',
    SQS_KEY: '',
    SQS_PREFIX: `https://sqs.ap-northeast-1.amazonaws.com/${ZINGER_STAGING_ACCOUNT}`,
    SQS_QUEUE: ZINGER_QUEUE_NAME,
    SQS_REGION: region,
    SQS_SECRET: '',
    ZINGER_FILENAME_DWS_COPAY_COORDINATION_CSV: 'ステージング_利用者負担上限額管理結果票_#{office}_#{providedIn}.csv',
    ZINGER_FILENAME_DWS_COPAY_COORDINATION_PDF: 'ステージング_利用者負担上限額管理結果票_#{office}_#{providedIn}.pdf',
    ZINGER_FILENAME_DWS_INVOICE_CSV: 'ステージング_介護給付費等請求書・明細書_#{office}_#{providedIn}.csv',
    ZINGER_FILENAME_DWS_INVOICE_PDF: 'ステージング_介護給付費等請求書・明細書_#{office}_#{providedIn}.pdf',
    ZINGER_FILENAME_DWS_PROJECT_PDF: 'ステージング_居宅介護計画書_%Y%m%d%H%M%S.xlsx',
    ZINGER_FILENAME_DWS_SERVICE_REPORT_CSV: 'ステージング_サービス提供実績記録票_#{office}_#{providedIn}.csv',
    ZINGER_FILENAME_DWS_SERVICE_REPORT_PDF: 'ステージング_サービス提供実績記録票_#{office}_#{providedIn}.pdf',
    ZINGER_FILENAME_LTCS_INVOICE_CSV: 'ステージング_介護給付費請求書・明細書_#{office}_#{providedIn}.csv',
    ZINGER_FILENAME_LTCS_INVOICE_PDF: 'ステージング_介護給付費請求書・明細書_#{office}_#{providedIn}.pdf',
    ZINGER_FILENAME_LTCS_PROJECT_PDF: 'ステージング_訪問介護計画書_%Y%m%d%H%M%S.xlsx',
    ZINGER_FILENAME_PLAN_TEMPLATE: 'ステージング_予定雛形_%Y%m%d%H%M%S.xlsx',
    ZINGER_HOST: '%s.staging.careid.net',
    ZINGER_INVITATION_LIFETIME_MINUTES: '10080',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_FOURTH_AUDIO_URI: '__UNDEFINED__',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_THIRD_AUDIO_URI: '__UNDEFINED__',
    ZINGER_URI_APP_DOMAIN: 'staging.careid.net'
  }),
  sandbox: toVariables({
    APP_DEBUG: 'true',
    APP_ENV: 'sandbox',
    MAIL_DRIVER: 'sendgrid',
    QUEUE_CONNECTION: 'redis',
    REDIS_SCHEME: 'tls',
    SQS_KEY: '',
    SQS_PREFIX: `https://sqs.ap-northeast-1.amazonaws.com/${ZINGER_SANDBOX_ACCOUNT}`,
    SQS_QUEUE: ZINGER_QUEUE_NAME,
    SQS_REGION: region,
    SQS_SECRET: '',
    ZINGER_FILENAME_DWS_PROJECT_PDF: 'サンドボックス_居宅介護計画書_%Y%m%d%H%M%S.xlsx',
    ZINGER_FILENAME_LTCS_PROJECT_PDF: 'サンドボックス_訪問介護計画書_%Y%m%d%H%M%S.xlsx',
    ZINGER_FILENAME_PLAN_TEMPLATE: 'サンドボックス_予定雛形_%Y%m%d%H%M%S.xlsx',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_FOURTH_AUDIO_URI: '__UNDEFINED__',
    ZINGER_STAFF_ATTENDANCE_CONFIRMATION_THIRD_AUDIO_URI: '__UNDEFINED__',
    ZINGER_URI_APP_DOMAIN: 'staging.careid.net'
  })
}
