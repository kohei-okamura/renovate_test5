/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BillingDestination } from '@zinger/enums/lib/billing-destination'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Addr } from '~/models/addr'

/**
 * 利用者：請求先情報.
 */
export type UserBillingDestination = Readonly<{
  /** 請求先 */
  destination: BillingDestination

  /** 支払方法 */
  paymentMethod: PaymentMethod

  /** 契約者番号 */
  contractNumber: string

  /** 請求先法人名・団体名 */
  corporationName: string

  /** 請求先氏名・担当者名 */
  agentName: string

  /** 請求先住所 */
  addr: Addr | undefined

  /** 請求先電話番号 */
  tel: string
}>
