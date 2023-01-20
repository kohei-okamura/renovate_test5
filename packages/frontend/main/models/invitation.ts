/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { OfficeId } from '~/models/office'
import { OfficeGroupId } from '~/models/office-group'
import { RoleId } from '~/models/role'

export type InvitationId = number

export type Invitation = {
  /** 招待 ID */
  id: InvitationId

  /** メールアドレス */
  email: string

  /** トークン */
  token: string

  /** ロール */
  roleIds: RoleId[]

  /** 事業所 */
  officeIds: OfficeId[]

  /** 事業所グループ */
  officeGroupIds: OfficeGroupId[]

  /** 有効期限 */
  expiredAt: DateLike

  /** 登録日時 */
  createdAt: DateLike
}
