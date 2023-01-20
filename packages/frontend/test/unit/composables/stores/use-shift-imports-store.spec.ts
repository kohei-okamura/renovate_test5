/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import {
  ShiftImportsState,
  ShiftImportsStore,
  useShiftImportsStore
} from '~/composables/stores/use-shift-imports-store'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/stores/use-shift-imports-store', () => {
  let store: ShiftImportsStore
  let state: ShiftImportsState

  beforeAll(() => {
    setupComposableTest()
    store = useShiftImportsStore()
    state = store.state
  })

  describe('state', () => {
    afterAll(() => {
      store.resetState()
    })

    it('should have 2 values (1 state, 1 getter)', () => {
      expect(keys(state)).toHaveLength(2)
    })

    describe('errors', () => {
      it('should be ref to undefined', () => {
        expect(state.errors).toBeRef()
        expect(state.errors?.value).toBeUndefined()
      })
    })
  })

  describe('getters', () => {
    afterAll(() => {
      store.resetState()
    })

    it('getErrors (errors is undefined)', () => {
      const n = 10

      expect(state.getErrors.value(n)).toBeUndefined()
    })

    it('getErrors (specify less than all)', async () => {
      const errorLength = 100
      const n = 10
      const errors = [...Array(errorLength).keys()].map(v => `エラー${v.toString()}`)

      await store.updateErrors(errors)

      expect(state.getErrors.value(n)).toHaveLength(n)
    })

    it('getErrors (specify more than all)', async () => {
      const errorLength = 100
      const n = 10000
      const errors = [...Array(errorLength).keys()].map(v => `エラー${v.toString()}`)

      await store.updateErrors(errors)

      expect(state.getErrors.value(n)).toHaveLength(errorLength)
    })
  })

  describe('actions', () => {
    afterEach(() => {
      store.resetState()
    })

    it('updateErrors', async () => {
      const errors = ['エラー1', 'エラー2', 'エラー3']

      expect(state.errors?.value).toBeUndefined()

      await store.updateErrors(errors)

      expect(state.errors?.value).toStrictEqual(errors)
    })

    it('resetState', async () => {
      expect(state.errors?.value).toBeUndefined()

      await store.updateErrors(['エラー1', 'エラー2', 'エラー3'])

      expect(state.errors?.value).not.toBeUndefined()

      await store.resetState()

      expect(state.errors?.value).toBeUndefined()

      // TODO こんな感じのができると良いのだけど、関数を返す getter を持っているとうまくいかない
      // expect(state).toMatchObject(useShiftImportsStore().store)
    })
  })
})
