/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { Expense } from '~/models/expense'
import { OfficeId } from '~/models/office'

/**
 * 自費サービス情報 ID.
 */
export type OwnExpenseProgramId = number

/**
 * 自費サービス情報.
 */
export type OwnExpenseProgram = Readonly<{
  /** 自費サービス情報 ID */
  id: OwnExpenseProgramId

  /** 事業所 ID */
  officeId: OfficeId | undefined

  /** 名称 */
  name: string

  /** 単位時間数 */
  durationMinutes: number

  /** 費用 */
  fee: Expense

  /** 備考 */
  note: string

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
