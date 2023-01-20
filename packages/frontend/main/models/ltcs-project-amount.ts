/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'

/**
 * 介護保険サービス：計画：サービス提供量.
 */
export type LtcsProjectAmount = Readonly<{
  /** 介護保険サービス：計画：サービス提供量区分 */
  category: LtcsProjectAmountCategory

  /** サービス時間 */
  amount: number
}>
