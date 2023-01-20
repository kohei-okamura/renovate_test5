/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Purpose } from '@zinger/enums/lib/purpose'
import { Addr } from '~/models/addr'
import { DateLike } from '~/models/date'
import { DwsAreaGradeId } from '~/models/dws-area-grade'
import { Location } from '~/models/location'
import { LtcsAreaGradeId } from '~/models/ltcs-area-grade'
import { OfficeGroupId } from '~/models/office-group'

/**
 * 事業所：障害福祉サービス
 */
type OfficeDwsGenericService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined

  /** 地域区分 ID */
  dwsAreaGradeId: DwsAreaGradeId | undefined
}>

/**
 * 事業所：障害福祉サービス（地域生活支援事業・移動支援）
 */
type OfficeDwsCommAccompanyService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined
}>

/**
 * 事業所：介護保険サービス：訪問介護
 */
type OfficeLtcsHomeVisitLongTermCareService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined

  /** 地域区分 ID */
  ltcsAreaGradeId: LtcsAreaGradeId | undefined
}>

/**
 * 事業所：介護保険サービス：居宅介護支援
 */
type OfficeLtcsCareManagementService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined

  /** 地域区分 ID */
  ltcsAreaGradeId: LtcsAreaGradeId | undefined
}>

/**
 * 事業所：介護保険サービス：訪問型サービス（総合事業）
 */
type OfficeLtcsCompHomeVisitingService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined
}>

/**
 * 事業所：介護保険サービス：介護予防支援
 */
type OfficeLtcsPreventionService = Readonly<{
  /** 事業所番号 */
  code: string

  /** 開設日 */
  openedOn: DateLike | undefined

  /** 指定更新期日 */
  designationExpiredOn: DateLike | undefined
}>

/**
 * 事業所 ID.
 */
export type OfficeId = number

/**
 * 事業所.
 */
export type Office = Readonly<{
  /** 事業所 ID */
  id: OfficeId

  /** 事業所名 */
  name: string

  /** 事業所名：略称 */
  abbr: string

  /** 事業所名：フリガナ */
  phoneticName: string

  /** 法人名 */
  corporationName: string

  /** 法人名：フリガナ */
  phoneticCorporationName: string

  /** 事業者区分 */
  purpose: Purpose

  /** 住所 */
  addr: Addr

  /** 位置情報 */
  location: Location

  /** 電話番号 */
  tel: string

  /** FAX番号 */
  fax: string

  /** メールアドレス */
  email: string

  /** 指定区分 */
  qualifications: OfficeQualification[]

  /** 事業所グループID */
  officeGroupId: OfficeGroupId | undefined

  /** 障害福祉サービス */
  dwsGenericService: OfficeDwsGenericService | undefined

  /** 障害福祉サービス：移動支援（地域生活支援事業） */
  dwsCommAccompanyService: OfficeDwsCommAccompanyService | undefined

  /** 介護保険サービス：訪問介護 */
  ltcsHomeVisitLongTermCareService: OfficeLtcsHomeVisitLongTermCareService | undefined

  /** 介護保険サービス：居宅介護支援 */
  ltcsCareManagementService: OfficeLtcsCareManagementService | undefined

  /** 介護保険サービス：訪問型サービス（総合事業） */
  ltcsCompHomeVisitingService: OfficeLtcsCompHomeVisitingService | undefined

  /** 介護保険サービス：介護予防支援 */
  ltcsPreventionService: OfficeLtcsPreventionService | undefined

  /** 状態 */
  status: OfficeStatus

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
