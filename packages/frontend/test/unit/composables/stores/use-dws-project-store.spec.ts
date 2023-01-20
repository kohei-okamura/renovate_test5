/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { DwsProjectStore, useDwsProjectStore } from '~/composables/stores/use-dws-project-store'
import { usePlugins } from '~/composables/use-plugins'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsProjectResponseStub } from '~~/stubs/create-dws-project-response-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-project-store', () => {
  const $api = createMockedApi('dwsProjects')
  const plugins = createMockedPlugins({ $api })
  let store: DwsProjectStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsProjectStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('dwsProject', () => {
      it('should be ref to undefined', () => {
        expect(store.state.dwsProject).toBeRef()
        expect(store.state.dwsProject.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 1
    const response = createDwsProjectResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsProjects, 'get').mockResolvedValue(response)
      store = useDwsProjectStore()
    })

    afterEach(() => {
      mocked($api.dwsProjects.get).mockReset()
    })

    it('should call $api.dwsProjects.get', async () => {
      await store.get({ id, userId })
      expect($api.dwsProjects.get).toHaveBeenCalledTimes(1)
      expect($api.dwsProjects.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.dwsProject', async () => {
      expect(store.state.dwsProject.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.dwsProject.value).toStrictEqual(response.dwsProject)
    })
  })

  describe('update', () => {
    const id = 1
    const userId = 1
    const contractId = 100
    const current = createDwsProjectResponseStub()
    const dwsProject = createDwsProjectStub(current.dwsProject.id, createContractStub(contractId))
    const updated = { dwsProject }
    const form = {
      officeId: dwsProject.officeId,
      staffId: dwsProject.staffId,
      writtenOn: dwsProject.writtenOn,
      effectivatedOn: dwsProject.effectivatedOn,
      requestFromUser: dwsProject.requestFromUser,
      requestFromFamily: dwsProject.requestFromFamily,
      programs: [...dwsProject.programs],
      objective: dwsProject.objective
    }

    beforeAll(() => {
      store = useDwsProjectStore()
      jest.spyOn($api.dwsProjects, 'get').mockResolvedValue(current)
      jest.spyOn($api.dwsProjects, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.dwsProjects.update', async () => {
      await store.update({ form, id, userId })
      expect($api.dwsProjects.update).toHaveBeenCalledTimes(1)
      expect($api.dwsProjects.update).toHaveBeenCalledWith({ form, id, userId })
    })

    it('should update state.dwsProject', async () => {
      expect(store.state.dwsProject.value).toStrictEqual(current.dwsProject)
      await store.update({ form, id, userId })
      expect(store.state.dwsProject.value).toStrictEqual(updated.dwsProject)
    })
  })
})
