/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { Sex } from '@zinger/enums/lib/sex'
import { DateLike } from '~/models/date'
import { LtcsInsCardId } from '~/models/ltcs-ins-card'
import { StructuredName } from '~/models/structured-name'
import { UserId } from '~/models/user'

/**
 * 介護保険サービス請求：利用者.
 */
export type LtcsBillingUser = Readonly<{
  /** 利用者 ID */
  userId: UserId

  /** 介護保険被保険者証 ID */
  ltcsInsCardId: LtcsInsCardId

  /** 被保険者証番号 */
  insNumber: string

  /** 氏名 */
  name: StructuredName

  /** 性別 */
  sex: Sex

  /** 生年月日 */
  birthday: DateLike

  /** 要介護状態区分 */
  ltcsLevel: LtcsLevel

  /** 認定の有効期間（開始） */
  activatedOn: DateLike

  /** 認定の有効期間（終了） */
  deactivatedOn: DateLike
}>
