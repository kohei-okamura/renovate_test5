/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { LtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { OfficeId } from '~/models/office'

/**
 * 介護保険サービス：請求：居宅サービス計画.
 */
export type LtcsCarePlanAuthor = Readonly<{
  /** 居宅サービス計画作成区分 */
  authorType: LtcsCarePlanAuthorType

  /** 事業所 */
  officeId: OfficeId | undefined

  /** 事業所番号 */
  code: string

  /** 事業所名 */
  name: string
}>
