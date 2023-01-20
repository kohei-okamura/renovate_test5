/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { VBreadcrumb } from '~/models/vuetify'

export const breadcrumb = (text: string, to?: string): VBreadcrumb => ({ text, to })
