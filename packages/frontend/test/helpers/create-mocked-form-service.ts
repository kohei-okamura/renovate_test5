/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { FormService } from '~/services/form-service'

export const createMockedFormService = () => createMock<FormService>({
  preventUnexpectedUnload: noop,
  watch: noop,
  submit: async (f: any) => { return await f() }
})
