/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsLevel } from '@zinger/enums/lib/dws-level'
import { DwsType } from '@zinger/enums/lib/dws-type'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { createDwsCertificationResponseStub } from '~~/stubs/create-dws-certification-response-stub'
import { createDwsCertificationStub } from '~~/stubs/create-dws-certification-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

type Form = DeepPartial<DwsCertificationsApi.Form>

describe('api/dws-certifications-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsCertifications: DwsCertificationsApi.Definition

  beforeEach(() => {
    dwsCertifications = DwsCertificationsApi.create(axios)
  })

  const form: Form = {
    child: {
      name: {
        familyName: '倉田',
        givenName: '綾',
        phoneticFamilyName: 'クラタ',
        phoneticGivenName: 'アヤ'
      },
      birthday: '1988-08-23'
    },
    effectivatedOn: '1995/01/20',
    status: DwsCertificationStatus.applied,
    dwsNumber: '0123456789',
    dwsTypes: [DwsType.physical],
    issuedOn: '1995/01/20',
    cityName: '東伯郡琴浦町',
    cityCode: '34033',
    dwsLevel: DwsLevel.level1,
    isSubjectOfComprehensiveSupport: true,
    activatedOn: '1995-01-20',
    deactivatedOn: '1995-01-20',
    grants: [
      {
        dwsCertificationServiceType: DwsCertificationServiceType.physicalCare,
        grantedAmount: 'amount',
        activatedOn: '1995-01-20',
        deactivatedOn: '1995-01-20'
      }
    ],
    copayLimit: 6894,
    copayActivatedOn: '1995-01-20',
    copayDeactivatedOn: '1995-01-20',
    copayCoordination: {},
    agreements: [
      {
        indexNumber: 2,
        officeId: 3,
        dwsCertificationAgreementType: DwsCertificationAgreementType.accompany,
        paymentAmount: 44520,
        agreedOn: '1995-01-20',
        expiredOn: '1995-01-20'
      }
    ]
  }

  describe('create', () => {
    const userId = 0

    it('should post /api/users/:userId/dws-certifications', async () => {
      const url = `/api/users/${userId}/dws-certifications`
      adapter.setup(x => {
        x.onPost().replyOnce(HttpStatusCode.Created)
      })

      await dwsCertifications.create({ form, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(`/api/users/${userId}/dws-certifications`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsCertifications.create({ form, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('delete', () => {
    const userId = 2
    const id = 1

    it('should delete /api/users/:userId/dws-certifications/:id', async () => {
      const url = `/api/users/${userId}/dws-certifications/${id}`
      adapter.setup(x => {
        x.onDelete(url).replyOnce(HttpStatusCode.NoContent)
      })

      await dwsCertifications.delete({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'delete', url })
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onDelete(`/api/users/${userId}/dws-certifications/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsCertifications.delete({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    it('should get /api/users/:userId/dws-certifications/:id', async () => {
      const id = 1
      const userId = 1
      const url = `/api/users/${userId}/dws-certifications/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsCertificationResponseStub(id))
      })

      await dwsCertifications.get({ id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const stub = createDwsCertificationStub()
      const id = stub.id
      const userId = stub.userId
      const expected = createDwsCertificationResponseStub(id)
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-certifications/${id}`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsCertifications.get({ id, userId })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      const userId = 2
      adapter.setup(x => {
        x.onGet(`/api/users/${userId}/dws-certifications/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsCertifications.get({ id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const userId = 1

    it('should put /api/users/:userId/dws-certifications/:id', async () => {
      const id = 1
      const url = `/api/users/${userId}/dws-certifications/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await dwsCertifications.update({ form, id, userId })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const id = 2
      adapter.setup(x => {
        x.onPut(`/api/users/${userId}/dws-certifications/${id}`).replyOnce(HttpStatusCode.BadRequest)
      })
      const promise = dwsCertifications.update({ form, id, userId })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
