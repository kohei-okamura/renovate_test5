/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Certification } from '@zinger/enums/lib/certification'
import { Sex } from '@zinger/enums/lib/sex'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { Addr } from '~/models/addr'
import { BankAccountId } from '~/models/bank-account'
import { DateLike } from '~/models/date'
import { Location } from '~/models/location'
import { OfficeId } from '~/models/office'
import { OfficeGroupId } from '~/models/office-group'
import { RoleId } from '~/models/role'
import { StructuredName } from '~/models/structured-name'

/**
 * スタッフ ID.
 */
export type StaffId = number

/**
 * スタッフ.
 */
export type Staff = Readonly<{
  /** スタッフ ID */
  id: StaffId

  /** 銀行口座 ID */
  bankAccountId: BankAccountId

  /** ロール ID */
  roleIds: RoleId[]

  /** 社員番号 */
  employeeNumber: string

  /** 氏名 */
  name: StructuredName

  /** 住所 */
  addr: Addr

  /** 性別 */
  sex: Sex

  /** 生年月日 */
  birthday: DateLike

  /** 位置情報 */
  location: Location

  /** 電話番号 */
  tel: string

  /** FAX 番号 */
  fax: string

  /** メールアドレス */
  email: string

  /** 資格 */
  certifications: Certification[]

  /** 事業所ID */
  officeIds: OfficeId[]

  /** 事業所グループID */
  officeGroupIds: OfficeGroupId[]

  /** メールアドレス検証済みフラグ */
  isVerified: boolean

  /** 状態 */
  status: StaffStatus

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
