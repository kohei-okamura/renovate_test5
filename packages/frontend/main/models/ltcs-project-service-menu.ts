/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { DateLike } from '~/models/date'

/**
 * 介護保険サービス：計画：サービス内容 ID.
 */
export type LtcsProjectServiceMenuId = number

/**
 * 介護保険サービス：計画：サービス内容.
 */
export type LtcsProjectServiceMenu = Readonly<{
  /** サービス内容 ID */
  id: LtcsProjectServiceMenuId

  /** サービス区分 */
  category: LtcsProjectServiceCategory

  /** 名称 */
  name: string

  /** 表示名 */
  displayName: string

  /** 表示順 */
  sortOrder: number

  /** 登録日時 */
  createdAt: DateLike
}>
