/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsGrantedServiceCode } from '@zinger/enums/lib/dws-granted-service-code'
import { DateLike } from '~/models/date'

/**
 * 障害福祉サービス：明細書：契約.
 */
export type DwsBillingStatementContract = Readonly<{
  /** 決定サービスコード */
  dwsGrantedServiceCode: DwsGrantedServiceCode

  /** 契約支給量（分単位） */
  grantedAmount: number

  /** 契約開始年月日 */
  agreedOn: DateLike

  /** 契約終了年月日 */
  expiredOn: DateLike

  /** 事業者記入欄番号 */
  indexNumber: number
}>
