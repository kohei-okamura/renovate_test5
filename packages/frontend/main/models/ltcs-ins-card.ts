/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { DateLike } from '~/models/date'
import { LtcsInsCardMaxBenefitQuota } from '~/models/ltcs-ins-card-max-benefit-quota'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'

/**
 * 介護保険被保険者証.
 */
export type LtcsInsCardId = number

/**
 * 介護保険被保険者証.
 */
export type LtcsInsCard = Readonly<{
  /** 介護保険被保険者証 ID */
  id: LtcsInsCardId

  /** 利用者 ID */
  userId: UserId

  /** 適用日 */
  effectivatedOn: DateLike

  /** 介護保険認定区分 */
  status: LtcsInsCardStatus

  /** 被保険者証番号 */
  insNumber: string

  /** 交付日 */
  issuedOn: DateLike

  /** 保険者番号 */
  insurerNumber: string

  /** 保険者名 */
  insurerName: string

  /** 要介護度・要介護状態区分等 */
  ltcsLevel: LtcsLevel

  /** 認定日 */
  certificatedOn: DateLike

  /** 認定の有効期間（開始） */
  activatedOn: DateLike

  /** 認定の有効期間（終了） */
  deactivatedOn: DateLike

  /** 種類支給限度基準額 */
  maxBenefitQuotas: LtcsInsCardMaxBenefitQuota[]

  /** 利用者負担割合（原則） */
  copayRate: number

  /** 利用者負担適用期間（開始） */
  copayActivatedOn: DateLike

  /** 利用者負担適用期間（終了） */
  copayDeactivatedOn: DateLike

  /** 居宅介護支援事業所：担当者 */
  careManagerName: string

  /** 居宅サービス計画作成区分 */
  carePlanAuthorType: LtcsCarePlanAuthorType

  /** 地域包括支援センター ID */
  communityGeneralSupportCenterId: OfficeId | undefined

  /** 居宅介護支援事業所 ID */
  carePlanAuthorOfficeId: OfficeId | undefined

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
