/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export const appKey = '/zinger/secure/appKey'
export const awsBucket = '/zinger/secure/awsBucket'
export const awsReadonlyBucket = '/zinger/secure/awsReadonlyBucket'
export const dbHost = '/zinger/string/dbHost'
export const dbMasterPassword = '/zinger/secure/dbMasterPassword'
export const dbPassword = '/zinger/secure/dbPassword'
export const dbUsername = '/zinger/secure/dbUsername'
export const mackerelApiKey = '/zinger/secure/mackerelApiKey'
export const mackerelExternalId = '/zinger/string/mackerelExternalId'
export const redisHost = '/zinger/string/redisHost'
export const redisPassword = '/zinger/secure/redisPassword'
export const zingerGoogleGeocodingApiKey = '/zinger/secure/zingerGoogleGeocodingApiKey'
export const zingerSendgridApiKey = '/zinger/secure/zingerSendgridApiKey'
export const zingerTwilioAccountSid = '/zinger/secure/zingerTwilioAccountSid'
export const zingerTwilioApiKeySecret = '/zinger/secure/zingerTwilioApiKeySecret'
export const zingerTwilioApiKeySid = '/zinger/secure/zingerTwilioApiKeySid'
export const zingerTwilioFromSmsNumber = '/zinger/secure/zingerTwilioFromSmsNumber'
export const zingerTwilioFromTelNumber = '/zinger/secure/zingerTwilioFromTelNumber'
export const zingerUrlShortenerKey = '/zinger/secure/zingerUrlShortenerKey'

export type SsmMackerelParameterName =
  typeof mackerelApiKey |
  typeof mackerelExternalId

export type SsmStringParameterName =
  typeof dbHost |
  typeof redisHost

export type SsmSecretParameterName =
  typeof appKey |
  typeof awsBucket |
  typeof awsReadonlyBucket |
  typeof dbMasterPassword |
  typeof dbPassword |
  typeof dbUsername |
  typeof redisPassword |
  typeof zingerGoogleGeocodingApiKey |
  typeof zingerSendgridApiKey |
  typeof zingerTwilioAccountSid |
  typeof zingerTwilioApiKeySecret |
  typeof zingerTwilioApiKeySid |
  typeof zingerTwilioFromSmsNumber |
  typeof zingerTwilioFromTelNumber |
  typeof zingerUrlShortenerKey

export type SsmRegisterName =
  SsmSecretParameterName |
  SsmMackerelParameterName

export type SsmParameterName = SsmStringParameterName | SsmSecretParameterName

export type SsmRegisters = Record<SsmRegisterName, string>
