/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DateLike } from '~/models/date'
import { OfficeId } from '~/models/office'

/**
 * 障害福祉サービス受給者証：訪問系サービス事業者記入欄.
 */
export type DwsCertificationAgreement = Readonly<{
  /** 番号 */
  indexNumber: number

  /** 事業所ID */
  officeId: OfficeId

  /** 障害福祉サービス受給者証 サービス内容 */
  dwsCertificationAgreementType: DwsCertificationAgreementType

  /** 契約支給量（分単位） */
  paymentAmount: number

  /** 契約日 */
  agreedOn: DateLike

  /** 当該契約支給量によるサービス提供終了日 */
  expiredOn: DateLike
}>
