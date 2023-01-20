/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { OwnExpenseProgramStore, useOwnExpenseProgramStore } from '~/composables/stores/use-own-expense-program-store'
import { usePlugins } from '~/composables/use-plugins'
import { createOwnExpenseProgramResponseStub } from '~~/stubs/create-own-expense-program-response-stub'
import { createOwnExpenseProgramStub } from '~~/stubs/create-own-expense-program-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('~/composables/stores/use-own-expense-program-store', () => {
  const $api = createMockedApi('ownExpensePrograms')
  const plugins = createMockedPlugins({ $api })
  let store: OwnExpenseProgramStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useOwnExpenseProgramStore()
    })

    it('should have 1 values', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('ownExpenseProgram', () => {
      it('should be ref to undefined', () => {
        expect(store.state.ownExpenseProgram).toBeRef()
        expect(store.state.ownExpenseProgram.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createOwnExpenseProgramResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ownExpensePrograms, 'get').mockResolvedValue(response)
      store = useOwnExpenseProgramStore()
    })

    afterEach(() => {
      mocked($api.ownExpensePrograms.get).mockReset()
    })

    it('should call $api.ownExpensePrograms.get', async () => {
      await store.get({ id })
      expect($api.ownExpensePrograms.get).toHaveBeenCalledTimes(1)
      expect($api.ownExpensePrograms.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.ownExpenseProgram', async () => {
      expect(store.state.ownExpenseProgram.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.ownExpenseProgram.value).toStrictEqual(response.ownExpenseProgram)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createOwnExpenseProgramResponseStub()
    const ownExpenseProgram = createOwnExpenseProgramStub(current.ownExpenseProgram.id)
    const updated = { ownExpenseProgram }
    const form = {
      name: ownExpenseProgram.name,
      durationMinutes: ownExpenseProgram.durationMinutes,
      fee: ownExpenseProgram.fee,
      note: ownExpenseProgram.note
    }

    beforeAll(() => {
      store = useOwnExpenseProgramStore()
      jest.spyOn($api.ownExpensePrograms, 'get').mockResolvedValue(current)
      jest.spyOn($api.ownExpensePrograms, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.ownExpensePrograms.update', async () => {
      await store.update({ form, id })
      expect($api.ownExpensePrograms.update).toHaveBeenCalledTimes(1)
      expect($api.ownExpensePrograms.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.ownExpenseProgram', async () => {
      expect(store.state.ownExpenseProgram.value).toStrictEqual(current.ownExpenseProgram)
      await store.update({ form, id })
      expect(store.state.ownExpenseProgram.value).toStrictEqual(updated.ownExpenseProgram)
    })
  })
})
