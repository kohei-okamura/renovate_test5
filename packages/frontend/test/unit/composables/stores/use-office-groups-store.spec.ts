/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { OfficeGroupsStore, useOfficeGroupsStore } from '~/composables/stores/use-office-groups-store'
import { usePlugins } from '~/composables/use-plugins'
import { createTree } from '~/models/tree'
import { createOfficeGroupIndexResponseStub } from '~~/stubs/create-office-group-index-response-stub'
import { createOfficeGroupResponseStub } from '~~/stubs/create-office-group-response-stub'
import { createOfficeGroupStub, OFFICE_GROUP_ID_MIN } from '~~/stubs/create-office-group-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-office-groups-store', () => {
  const $api = createMockedApi('officeGroups')
  const plugins = createMockedPlugins({ $api })
  let store: OfficeGroupsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useOfficeGroupsStore()
    })

    it('should have 4 values (2 states, 2 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('officeGroups', () => {
      it('should be ref to empty array', () => {
        expect(store.state.officeGroups).toBeRef()
        expect(store.state.officeGroups.value).toBeEmptyArray()
      })
    })

    describe('loading', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingOfficeGroups).toBeRef()
        expect(store.state.isLoadingOfficeGroups.value).toBeFalse()
      })
    })

    describe('officeGroupOptions', () => {
      it('should be ref to empty array', () => {
        expect(store.state.officeGroupOptions).toBeRef()
        expect(store.state.officeGroupOptions.value).toBeEmptyArray()
      })
    })

    describe('officeGroupsTree', () => {
      it('should be ref to empty array', () => {
        expect(store.state.officeGroupsTree).toBeRef()
        expect(store.state.officeGroupsTree.value).toBeEmptyArray()
      })
    })
  })

  describe('getIndex', () => {
    const response = createOfficeGroupIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.officeGroups, 'getIndex').mockResolvedValue(response)
      store = useOfficeGroupsStore()
    })

    afterEach(() => {
      mocked($api.officeGroups.getIndex).mockReset()
    })

    it('should call $api.officeGroups.getIndex', async () => {
      await store.getIndex()
      expect($api.officeGroups.getIndex).toHaveBeenCalledTimes(1)
      expect($api.officeGroups.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.officeGroups', async () => {
      expect(store.state.officeGroups.value).not.toStrictEqual(response.list)
      await store.getIndex()
      expect(store.state.officeGroups.value).toStrictEqual(response.list)
    })

    it('should update state.loading', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.officeGroups, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingOfficeGroups.value).toBeFalse()

      const promise = store.getIndex()

      expect(store.state.isLoadingOfficeGroups.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingOfficeGroups.value).toBeFalse()
    })

    it('should update state.officeGroupOptions', async () => {
      const xs = response.list.map(x => ({ text: x.name, value: x.id }))
      expect(store.state.officeGroupOptions.value).not.toStrictEqual(xs)

      await store.getIndex()

      expect(store.state.officeGroupOptions.value).toStrictEqual(xs)
    })

    it('should update state.officeGroupsTree', async () => {
      const tree = createTree(response.list, 'parentOfficeGroupId')
      expect(store.state.officeGroupsTree.value).not.toStrictEqual(tree)

      await store.getIndex()

      expect(store.state.officeGroupsTree.value).toStrictEqual(tree)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createOfficeGroupResponseStub(OFFICE_GROUP_ID_MIN)
    const indexResponse = createOfficeGroupIndexResponseStub()
    const officeGroup = createOfficeGroupStub(current.officeGroup.id)
    const updated = { officeGroup }
    const form = {
      name: officeGroup?.name,
      sortOrder: officeGroup?.sortOrder
    }

    beforeAll(() => {
      store = useOfficeGroupsStore()
      jest.spyOn($api.officeGroups, 'getIndex').mockResolvedValue(indexResponse)
      jest.spyOn($api.officeGroups, 'update').mockResolvedValue(indexResponse)
    })

    beforeEach(async () => {
      await store.getIndex()
    })

    it('should call $api.officeGroups.update', async () => {
      await store.update({ form, id })
      expect($api.officeGroups.update).toHaveBeenCalledTimes(1)
      expect($api.officeGroups.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.officeGroup', async () => {
      expect(store.state.officeGroups.value[0]).toStrictEqual(current.officeGroup)
      await store.update({ form, id })
      expect(store.state.officeGroups.value[0]).toStrictEqual(updated.officeGroup)
    })
  })
})
