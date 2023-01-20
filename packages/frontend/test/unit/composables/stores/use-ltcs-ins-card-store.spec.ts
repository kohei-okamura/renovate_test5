/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { LtcsInsCardStore, useLtcsInsCardStore } from '~/composables/stores/use-ltcs-ins-card-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsInsCardResponseStub } from '~~/stubs/create-ltcs-ins-card-response-stub'
import { createLtcsInsCardStub } from '~~/stubs/create-ltcs-ins-card-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-ins-card-store', () => {
  const $api = createMockedApi('ltcsInsCards')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsInsCardStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsInsCardStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('ltcsInsCard', () => {
      it('should be ref to undefined', () => {
        expect(store.state.ltcsInsCard).toBeRef()
        expect(store.state.ltcsInsCard.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 517
    const response = createLtcsInsCardResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ltcsInsCards, 'get').mockResolvedValue(response)
      store = useLtcsInsCardStore()
    })

    afterEach(() => {
      mocked($api.ltcsInsCards.get).mockReset()
    })

    it('should call $api.ltcsInsCards.get', async () => {
      await store.get({ id, userId })
      expect($api.ltcsInsCards.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsInsCards.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.ltcsInsCard', async () => {
      expect(store.state.ltcsInsCard.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.ltcsInsCard.value).toStrictEqual(response.ltcsInsCard)
    })
  })

  describe('update', () => {
    const id = 1
    const userId = 1
    const current = createLtcsInsCardResponseStub()
    const ltcsInsCard = createLtcsInsCardStub(current.ltcsInsCard.id)
    const updated = { ltcsInsCard }
    const form = {
      activatedOn: ltcsInsCard.activatedOn,
      certificatedOn: ltcsInsCard.certificatedOn,
      copayActivatedOn: ltcsInsCard.copayActivatedOn,
      copayDeactivatedOn: ltcsInsCard.copayDeactivatedOn,
      copayRate: ltcsInsCard.copayRate,
      deactivatedOn: ltcsInsCard.deactivatedOn,
      effectivatedOn: ltcsInsCard.effectivatedOn,
      insNumber: ltcsInsCard.insNumber,
      insurerName: ltcsInsCard.insurerName,
      insurerNumber: ltcsInsCard.insurerNumber,
      issuedOn: ltcsInsCard.issuedOn,
      ltcsLevel: ltcsInsCard.ltcsLevel,
      maxBenefitQuotas: [...ltcsInsCard.maxBenefitQuotas],
      status: ltcsInsCard.status
    }

    beforeAll(() => {
      store = useLtcsInsCardStore()
      jest.spyOn($api.ltcsInsCards, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsInsCards, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.ltcsInsCards.update', async () => {
      await store.update({ form, id, userId })
      expect($api.ltcsInsCards.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsInsCards.update).toHaveBeenCalledWith({ form, id, userId })
    })

    it('should update state.ltcsInsCard', async () => {
      expect(store.state.ltcsInsCard.value).toStrictEqual(current.ltcsInsCard)
      await store.update({ form, id, userId })
      expect(store.state.ltcsInsCard.value).toStrictEqual(updated.ltcsInsCard)
    })
  })
})
