/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceMenuId } from '~/models/ltcs-project-service-menu'

/**
 * 計画：週間サービス計画：サービス詳細.
 */
export type LtcsProjectContent = Readonly<{
  /** 介護保険サービス：計画：サービス内容 ID */
  menuId: LtcsProjectServiceMenuId

  /** 所要時間 */
  duration: number | undefined

  /** サービスの具体的内容 */
  content: string

  /** 留意事項 */
  memo: string
}>
