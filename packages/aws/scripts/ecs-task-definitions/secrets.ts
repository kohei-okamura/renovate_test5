/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { SecretList } from 'aws-sdk/clients/ecs'
import { SecretName } from '~aws/scripts/ecs-task-definitions/environment-name'
import {
  appKey,
  awsBucket,
  awsReadonlyBucket,
  dbHost,
  dbPassword,
  dbUsername,
  redisHost,
  redisPassword,
  SsmParameterName,
  zingerGoogleGeocodingApiKey,
  zingerSendgridApiKey,
  zingerTwilioAccountSid,
  zingerTwilioApiKeySecret,
  zingerTwilioApiKeySid,
  zingerTwilioFromSmsNumber,
  zingerTwilioFromTelNumber,
  zingerUrlShortenerKey
} from '~aws/scripts/ssm-parameters/names'

const secretsDef: Record<SecretName, SsmParameterName> = {
  APP_KEY: appKey,
  AWS_BUCKET: awsBucket,
  AWS_READONLY_BUCKET: awsReadonlyBucket,
  DB_HOST: dbHost,
  DB_PASSWORD: dbPassword,
  DB_USERNAME: dbUsername,
  REDIS_HOST: redisHost,
  REDIS_PASSWORD: redisPassword,
  ZINGER_GOOGLE_GEOCODING_API_KEY: zingerGoogleGeocodingApiKey,
  ZINGER_SENDGRID_API_KEY: zingerSendgridApiKey,
  ZINGER_TWILIO_ACCOUNT_SID: zingerTwilioAccountSid,
  ZINGER_TWILIO_API_KEY_SECRET: zingerTwilioApiKeySecret,
  ZINGER_TWILIO_API_KEY_SID: zingerTwilioApiKeySid,
  ZINGER_TWILIO_FROM_SMS_NUMBER: zingerTwilioFromSmsNumber,
  ZINGER_TWILIO_FROM_TEL_NUMBER: zingerTwilioFromTelNumber,
  ZINGER_URL_SHORTENER_KEY: zingerUrlShortenerKey
}

export const secrets: SecretList = Object.entries(secretsDef).map(([name, valueFrom]) => ({
  name,
  valueFrom: valueFrom!
}))
