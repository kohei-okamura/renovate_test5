/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Deferred } from 'ts-deferred'
import { useAxios } from '~/composables/use-axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/use-axios', () => {
  setupComposableTest()

  describe('errors', () => {
    it('should be ref', () => {
      const { errors } = useAxios()
      expect(errors).toBeRef()
    })

    it('should be empty object', () => {
      const { errors } = useAxios()
      expect(errors.value).toStrictEqual({})
    })
  })

  describe('progress', () => {
    it('should be ref', () => {
      const { progress } = useAxios()
      expect(progress).toBeRef()
    })

    it('should be false', () => {
      const { progress } = useAxios()
      expect(progress.value).toBeFalse()
    })
  })

  describe('withAxios', () => {
    it('should be function', () => {
      const { withAxios } = useAxios()
      expect(withAxios).toBeFunction()
    })

    it('should update "progress" to true during calling the closure', async () => {
      const { progress, withAxios } = useAxios()
      expect(progress.value).toBeFalse()
      const deferred = new Deferred<void>()
      const promise = withAxios(() => deferred.promise)
      expect(progress.value).toBeTrue()
      deferred.resolve()
      await promise
      expect(progress.value).toBeFalse()
    })

    it('should update "errors" when the closure throws AxiosError', async () => {
      const { errors, withAxios } = useAxios()
      expect(errors.value).toStrictEqual({})
      await withAxios(() => {
        throw createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            someField: ['Some field is required']
          }
        })
      })
      expect(errors.value).toStrictEqual({
        someField: ['Some field is required']
      })
    })

    it('should throw error when the closure throw Error but it it not AxiosError', async () => {
      const { withAxios } = useAxios()
      const promise = withAxios(() => {
        throw new Error('Some error message')
      })
      await expect(promise).rejects.toThrow('Some error message')
    })
  })
})
