/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceMenuId } from '~/models/dws-project-service-menu'

/**
 * 障害福祉サービス：計画：サービス詳細.
 */
export type DwsProjectContent = Readonly<{
  /** サービス内容 ID */
  menuId: DwsProjectServiceMenuId

  /** 所要時間 */
  duration: number | undefined

  /** サービスの具体的内容 */
  content: string

  /** 留意事項 */
  memo: string
}>
