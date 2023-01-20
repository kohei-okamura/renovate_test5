/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { DwsBillingId } from '~/models/dws-billing'
import { HttpStatusCode } from '~/models/http-status-code'
import { CopayListsApi } from '~/services/api/copay-lists-api'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

const endpoint = 'copay-lists'

describe(`api/${endpoint}-api`, () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)

  let copayLists: CopayListsApi.Definition

  beforeEach(() => {
    copayLists = CopayListsApi.create(axios)
  })

  describe('download', () => {
    const form: CopayListsApi.DownloadForm = { ids: [5], isDivided: false }
    const billingId: DwsBillingId = 1
    const url = `/api/dws-billings/${billingId}/${endpoint}`

    it(`should post ${url}`, async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await copayLists.download({ form, billingId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = copayLists.download({ form, billingId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
