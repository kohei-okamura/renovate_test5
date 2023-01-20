/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { DateLike } from '~/models/date'

/**
 * サービス内容 ID.
 */
export type DwsProjectServiceMenuId = number

/**
 * 障害福祉：計画：サービス内容.
 */
export type DwsProjectServiceMenu = Readonly<{
  /** サービス内容 ID */
  id: DwsProjectServiceMenuId

  /** サービス区分 */
  category: DwsProjectServiceCategory

  /** 名称 */
  name: string

  /** 表示名 */
  displayName: string

  /** 表示順 */
  sortOrder: number

  /** 登録日時 */
  createdAt: DateLike
}>
