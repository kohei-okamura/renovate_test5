/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { TaxCategory } from '@zinger/enums/lib/tax-category'
import { TaxType } from '@zinger/enums/lib/tax-type'

/**
 * 費用.
 */
export type Expense = Readonly<{
  /** 費用（税抜） */
  taxExcluded: number

  /** 費用（税込） */
  taxIncluded: number

  /** 課税区分 */
  taxType: TaxType

  /** 税率区分 */
  taxCategory: TaxCategory
}>
