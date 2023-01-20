/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { LtcsProjectStore, useLtcsProjectStore } from '~/composables/stores/use-ltcs-project-store'
import { usePlugins } from '~/composables/use-plugins'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-project-store', () => {
  const $api = createMockedApi('ltcsProjects')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsProjectStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsProjectStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('ltcsProject', () => {
      it('should be ref to undefined', () => {
        expect(store.state.ltcsProject).toBeRef()
        expect(store.state.ltcsProject.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 1
    const response = createLtcsProjectResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ltcsProjects, 'get').mockResolvedValue(response)
      store = useLtcsProjectStore()
    })

    afterEach(() => {
      mocked($api.ltcsProjects.get).mockReset()
    })

    it('should call $api.ltcsProjects.get', async () => {
      await store.get({ id, userId })
      expect($api.ltcsProjects.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsProjects.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.ltcsProject', async () => {
      expect(store.state.ltcsProject.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.ltcsProject.value).toStrictEqual(response.ltcsProject)
    })
  })

  describe('update', () => {
    const id = 1
    const userId = 1
    const contractId = 100
    const current = createLtcsProjectResponseStub()
    const ltcsProject = createLtcsProjectStub(current.ltcsProject.id, createContractStub(contractId))
    const updated = { ltcsProject }
    const form = {
      officeId: ltcsProject.officeId,
      staffId: ltcsProject.staffId,
      writtenOn: ltcsProject.writtenOn,
      effectivatedOn: ltcsProject.effectivatedOn,
      requestFromUser: ltcsProject.requestFromUser,
      requestFromFamily: ltcsProject.requestFromFamily,
      problem: ltcsProject.problem,
      programs: [...ltcsProject.programs],
      shortTermObjective: ltcsProject.shortTermObjective,
      longTermObjective: ltcsProject.longTermObjective
    }

    beforeAll(() => {
      store = useLtcsProjectStore()
      jest.spyOn($api.ltcsProjects, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsProjects, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.ltcsProjects.update', async () => {
      await store.update({ form, id, userId })
      expect($api.ltcsProjects.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsProjects.update).toHaveBeenCalledWith({ form, id, userId })
    })

    it('should update state.ltcsProject', async () => {
      expect(store.state.ltcsProject.value).toStrictEqual(current.ltcsProject)
      await store.update({ form, id, userId })
      expect(store.state.ltcsProject.value).toStrictEqual(updated.ltcsProject)
    })
  })
})
