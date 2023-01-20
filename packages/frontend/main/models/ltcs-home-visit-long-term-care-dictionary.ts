/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'

/**
 * 介護保険サービス：訪問介護：サービスコード辞書 ID.
 */
export type LtcsHomeVisitLongTermCareDictionaryId = number

/**
 * 介護保険サービス：訪問介護：サービスコード辞書.
 */
export type LtcsHomeVisitLongTermCareDictionary = {
  /** 辞書 ID */
  id: LtcsHomeVisitLongTermCareDictionaryId

  /** 適用開始日 */
  effectivatedOn: DateLike

  /** 名前 */
  name: string

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}
