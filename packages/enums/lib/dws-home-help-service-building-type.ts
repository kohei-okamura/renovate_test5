/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  over20: 1,
  over50: 2
} as const

/**
 * 障害福祉サービス：居宅介護：建物区分.
 */
export type DwsHomeHelpServiceBuildingType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsHomeHelpServiceBuildingType = createEnumerable($$, [
  [$$.none, '下記に該当しない'],
  [$$.over20, '事業所と同一建物の利用者又はこれ以外の同一建物の利用者20人以上にサービスを行う場合'],
  [$$.over50, '事業所と同一建物の利用者50人以上にサービスを行う場合']
])

export const resolveDwsHomeHelpServiceBuildingType = DwsHomeHelpServiceBuildingType.resolve
