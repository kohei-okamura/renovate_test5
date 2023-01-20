/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingFile } from '~/models/dws-billing-file'
import { LtcsBillingFile } from '~/models/ltcs-billing-file'

export type BillingFile = DwsBillingFile | LtcsBillingFile
