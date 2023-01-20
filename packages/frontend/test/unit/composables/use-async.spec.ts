/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import flushPromises from 'flush-promises'
import { Deferred } from 'ts-deferred'
import { useAsync } from '~/composables/use-async'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/use-async', () => {
  beforeAll(() => {
    setupComposableTest()
  })

  describe('isRejected', () => {
    it('should be Ref', () => {
      const { isRejected } = useAsync(() => new Promise(noop))
      expect(isRejected).toBeRef()
    })

    it('should be false when the promise is not rejected/resolved', async () => {
      const { isRejected } = useAsync(() => new Promise(noop))
      await flushPromises()
      expect(isRejected.value).toBeFalse()
    })

    it('should be false when the promise is resolved', async () => {
      const { isRejected } = useAsync(() => Promise.resolve())
      await flushPromises()
      expect(isRejected.value).toBeFalse()
    })

    it('should be true when the promise is rejected #1', async () => {
      const { isRejected } = useAsync(() => Promise.reject(new Error('error message')))
      await flushPromises()
      expect(isRejected.value).toBeTrue()
    })

    it('should be true when the promise is rejected #2', async () => {
      const deferred = new Deferred()
      const { isRejected } = useAsync(() => deferred.promise)
      expect(isRejected.value).toBeFalse()
      deferred.reject(new Error('error message'))
      await flushPromises()
      expect(isRejected.value).toBeTrue()
    })
  })

  describe('isResolved', () => {
    it('should be Ref', () => {
      const { isResolved } = useAsync(() => new Promise(noop))
      expect(isResolved).toBeRef()
    })

    it('should be false when the promise is not rejected/resolved', async () => {
      const { isResolved } = useAsync(() => new Promise(noop))
      await flushPromises()
      expect(isResolved.value).toBeFalse()
    })

    it('should be true when the promise is rejected', async () => {
      const { isResolved } = useAsync(() => Promise.reject(new Error('error message')))
      await flushPromises()
      expect(isResolved.value).toBeFalse()
    })

    it('should be true when the promise is resolved #1', async () => {
      const { isResolved } = useAsync(() => Promise.resolve())
      await flushPromises()
      expect(isResolved.value).toBeTrue()
    })

    it('should be true when the promise is resolved #2', async () => {
      const deferred = new Deferred()
      const { isResolved } = useAsync(() => deferred.promise)
      expect(isResolved.value).toBeFalse()
      deferred.resolve()
      await flushPromises()
      expect(isResolved.value).toBeTrue()
    })
  })

  describe('rejectedValue', () => {
    it('should be Ref', () => {
      const { rejectedValue } = useAsync(() => new Promise(noop))
      expect(rejectedValue).toBeRef()
    })

    it('should be undefined when the promise is not rejected/resolved', async () => {
      const { rejectedValue } = useAsync(() => new Promise(noop))
      await flushPromises()
      expect(rejectedValue.value).toBeUndefined()
    })

    it('should be undefined when the promise is resolved', async () => {
      const { rejectedValue } = useAsync(() => Promise.resolve())
      await flushPromises()
      expect(rejectedValue.value).toBeUndefined()
    })

    it('should be error when the promise is rejected #1', async () => {
      const error = new Error('error message')
      const { rejectedValue } = useAsync(() => Promise.reject(error))
      await flushPromises()
      expect(rejectedValue.value).toBe(error)
    })

    it('should be error when the promise is rejected #1', async () => {
      const error = new Error('error message')
      const deferred = new Deferred()
      const { rejectedValue } = useAsync(() => deferred.promise)
      expect(rejectedValue.value).toBeUndefined()
      deferred.reject(error)
      await flushPromises()
      expect(rejectedValue.value).toBe(error)
    })
  })

  describe('resolvedValue', () => {
    it('should be Ref', () => {
      const { resolvedValue } = useAsync(() => new Promise(noop))
      expect(resolvedValue).toBeRef()
    })

    it('should be undefined when the promise is not rejected/resolved', async () => {
      const { resolvedValue } = useAsync(() => new Promise(noop))
      await flushPromises()
      expect(resolvedValue.value).toBeUndefined()
    })

    it('should be undefined when the promise is rejected', async () => {
      const { resolvedValue } = useAsync(() => Promise.reject(new Error('error message')))
      await flushPromises()
      expect(resolvedValue.value).toBeUndefined()
    })

    it('should be error when the promise is resolved #1', async () => {
      const value = {}
      const { resolvedValue } = useAsync(() => Promise.resolve(value))
      await flushPromises()
      expect(resolvedValue.value).toBe(value)
    })

    it('should be error when the promise is resolved #1', async () => {
      const value = {}
      const deferred = new Deferred()
      const { resolvedValue } = useAsync(() => deferred.promise)
      expect(resolvedValue.value).toBeUndefined()
      deferred.resolve(value)
      await flushPromises()
      expect(resolvedValue.value).toBe(value)
    })
  })
})
