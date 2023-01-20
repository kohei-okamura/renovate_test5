/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  notificationEnabled: 100001,
  oneOff: 100002,
  firstTime: 300001,
  emergency: 300002,
  sucking: 300003,
  welfareSpecialistCooperation: 301101,
  plannedByNovice: 301102,
  providedByBeginner: 301103,
  providedByCareWorkerForPwsd: 301104,
  over20: 301105,
  over50: 301106,
  behavioralDisorderSupportCooperation: 301201,
  hospitalized: 301202,
  longHospitalized: 301203,
  coaching: 301204,
  vitalFunctionsImprovement1: 401101,
  vitalFunctionsImprovement2: 401102
} as const

/**
 * サービスオプション.
 */
export type ServiceOption = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const ServiceOption = createEnumerable($$, [
  [$$.notificationEnabled, '通知'],
  [$$.oneOff, '単発'],
  [$$.firstTime, '初回'],
  [$$.emergency, '緊急時対応'],
  [$$.sucking, '喀痰吸引'],
  [$$.welfareSpecialistCooperation, '福祉専門職員等連携'],
  [$$.plannedByNovice, '初計'],
  [$$.providedByBeginner, '基礎研修課程修了者等'],
  [$$.providedByCareWorkerForPwsd, '重研'],
  [$$.over20, '同一建物減算'],
  [$$.over50, '同一建物減算（大規模）'],
  [$$.behavioralDisorderSupportCooperation, '行動障害支援連携'],
  [$$.hospitalized, '入院'],
  [$$.longHospitalized, '入院（長期）'],
  [$$.coaching, '熟練同行'],
  [$$.vitalFunctionsImprovement1, '生活機能向上連携Ⅰ'],
  [$$.vitalFunctionsImprovement2, '生活機能向上連携Ⅱ']
])

export const resolveServiceOption = ServiceOption.resolve
