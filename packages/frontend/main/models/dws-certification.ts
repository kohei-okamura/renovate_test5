/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import { Child } from '~/models/child'
import { CopayCoordination } from '~/models/copay-coordination'
import { DateLike } from '~/models/date'
import { DwsCertificationAgreement } from '~/models/dws-certification-agreement'
import { DwsCertificationGrant } from '~/models/dws-certification-grant'
import { UserId } from '~/models/user'

/**
 * 障害福祉サービス受給者証 ID.
 */
export type DwsCertificationId = number

/**
 * 障害福祉サービス受給者証.
 */
export type DwsCertification = Readonly<{
  /** 障害福祉サービス受給者証 ID */
  id: DwsCertificationId

  /** 利用者 ID */
  userId: UserId

  /** 適用日 */
  effectivatedOn: DateLike

  /** 障害福祉サービス認定区分 */
  status: DwsCertificationStatus

  /** 受給者証番号 */
  dwsNumber: string

  /** 障害種別 */
  dwsTypes: DwsType[]

  /** 交付日 */
  issuedOn: DateLike

  /** 市区町村名 */
  cityName: string

  /** 市区町村番号 */
  cityCode: string

  /** 障害程度区分 */
  dwsLevel: DwsLevel

  /** 重度障害者等包括支援対象フラグ */
  isSubjectOfComprehensiveSupport: boolean

  /** 認定の有効期間（開始） */
  activatedOn: DateLike

  /** 認定の有効期間（終了） */
  deactivatedOn: DateLike

  /** 支給量 */
  grants: DwsCertificationGrant[]

  /** 児童情報 */
  child: Child

  /** 利用者負担割合（原則） */
  copayRate: number

  /** 負担上限月額 */
  copayLimit: number

  /** 利用者負担適用期間（開始） */
  copayActivatedOn: DateLike

  /** 利用者負担適用期間（終了） */
  copayDeactivatedOn: DateLike

  /** 上限管理情報 */
  copayCoordination: CopayCoordination

  /** 訪問系サービス事業者記入欄 */
  agreements: DwsCertificationAgreement[]

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
