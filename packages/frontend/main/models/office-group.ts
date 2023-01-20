/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'

/**
 * 事業所グループ.
 */
export type OfficeGroupId = number

/**
 * 事業所グループ.
 */
export type OfficeGroup = Readonly<{
  /** 事業所グループ ID */
  id: OfficeGroupId

  /** 親事業所グループ ID */
  parentOfficeGroupId?: OfficeGroupId

  /** 名称 */
  name: string

  /** 表示順 */
  sortOrder: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
