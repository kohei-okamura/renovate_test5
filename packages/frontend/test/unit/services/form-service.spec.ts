/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick, ref } from '@nuxtjs/composition-api'
import { assert, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import Vue, { ComponentOptions } from 'vue'
import { onBeforeRouteLeave } from '~/composables/vue-router-compat'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { createFormService, FormService } from '~/services/form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/vue-router-compat')

describe('services/form-service', () => {
  setupComposableTest()

  const $confirm = createMock<ConfirmDialogService>()
  let $form: FormService

  beforeEach(() => {
    $form = createFormService({ $confirm })
  })

  afterEach(() => {
    mocked(onBeforeRouteLeave).mockReset()
  })

  describe('preventUnexpectedUnload', () => {
    it('should call onBeforeRouteLeave', () => {
      $form.preventUnexpectedUnload()
      expect(onBeforeRouteLeave).toHaveBeenCalledTimes(1)
    })
  })

  describe('watch', () => {
    beforeEach(() => {
      jest.spyOn(window, 'addEventListener')
      jest.spyOn(window, 'removeEventListener')
    })

    afterEach(() => {
      mocked(window.addEventListener).mockReset()
      mocked(window.removeEventListener).mockReset()
    })

    it('should add beforeunload handler when the value is true', () => {
      $form.watch(() => true)

      expect(window.removeEventListener).not.toHaveBeenCalled()
      expect(window.addEventListener).toHaveBeenCalledTimes(1)
      expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.anything())
    })

    it('should remove beforeunload handler when the value is false', () => {
      $form.watch(() => false)

      expect(window.addEventListener).not.toHaveBeenCalled()
      expect(window.removeEventListener).toHaveBeenCalledTimes(1)
      expect(window.removeEventListener).toHaveBeenCalledWith('beforeunload', expect.anything())
    })

    it('should add beforeunload handler when the value modified to true', async () => {
      const changed = ref(false)

      $form.watch(changed)

      expect(window.addEventListener).not.toHaveBeenCalled()
      expect(window.removeEventListener).toHaveBeenCalledTimes(1)

      changed.value = true
      await nextTick()

      expect(window.addEventListener).toHaveBeenCalledTimes(1)
      expect(window.removeEventListener).toHaveBeenCalledTimes(1)
    })

    it('should remove beforeunload handler when the value modified to false', async () => {
      const changed = ref(true)

      $form.watch(changed)

      expect(window.removeEventListener).not.toHaveBeenCalled()
      expect(window.addEventListener).toHaveBeenCalledTimes(1)

      changed.value = false
      await nextTick()

      expect(window.removeEventListener).toHaveBeenCalledTimes(1)
      expect(window.addEventListener).toHaveBeenCalledTimes(1)
    })
  })

  describe('submit', () => {
    // `submit` 中にページ遷移を抑制しない制御については `onBeforeRouteLeave` でテストする

    it('should call the given function', async () => {
      const spy = jest.fn()

      await $form.submit(spy)

      expect(spy).toHaveBeenCalledTimes(1)
      expect(spy).toHaveBeenCalledWith()
    })
  })

  describe('onBeforeRouteLeave', () => {
    const to = createMockedRoute()
    const from = createMockedRoute()
    const next = jest.fn()

    let callback: NonNullable<ComponentOptions<Vue>['beforeRouteLeave']>

    beforeEach(() => {
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      mocked(onBeforeRouteLeave).mockImplementation(f => {
        assert(typeof f === 'function', '1st argument of onBeforeRouteLeave should be a function')
        callback = f
      })
      $form.preventUnexpectedUnload()
    })

    afterEach(() => {
      next.mockReset()
      mocked($confirm.show).mockReset()
    })

    describe('when the target is not changed', () => {
      beforeEach(() => {
        $form.watch(() => false)
      })

      it('should not call $confirm.show', async () => {
        await callback(to, from, next)

        expect($confirm.show).not.toHaveBeenCalled()
      })

      it('should call next()', async () => {
        await callback(to, from, next)

        expect(next).toHaveBeenCalledTimes(1)
        expect(next).toHaveBeenCalledWith()
      })
    })

    describe('when the target is changed', () => {
      beforeEach(() => {
        $form.watch(() => true)
      })

      it('should call $confirm.show', async () => {
        await callback(to, from, next)

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          message: 'このページを離れますか？\n\n入力中の内容はまだ保存されていません。',
          negative: 'キャンセル',
          positive: 'ページを離れる'
        })
      })

      it('should call next() if the user confirmed', async () => {
        mocked($confirm.show).mockResolvedValue(true)

        await callback(to, from, next)

        expect(next).toHaveBeenCalledTimes(1)
        expect(next).toHaveBeenCalledWith()
      })

      it('should call next(false) if the user does not confirmed', async () => {
        mocked($confirm.show).mockResolvedValue(false)

        await callback(to, from, next)

        expect(next).toHaveBeenCalledTimes(1)
        expect(next).toHaveBeenCalledWith(false)
      })

      it('should not call $confirm.show if submit is in progress', async () => {
        // submit に渡す関数の戻り値の Promise を解決しないままにすることで処理中を表現する
        // noinspection ES6MissingAwait
        $form.submit(() => new Promise(noop))

        await callback(to, from, next)

        expect($confirm.show).not.toHaveBeenCalled()
      })

      it('should call next() if submit is in progress', async () => {
        // submit に渡す関数の戻り値の Promise を解決しないままにすることで処理中を表現する
        // noinspection ES6MissingAwait
        $form.submit(() => new Promise(noop))

        await callback(to, from, next)

        expect(next).toHaveBeenCalledTimes(1)
        expect(next).toHaveBeenCalledWith()
      })
    })
  })

  describe('verifyBeforeLeaving', () => {
    const next = jest.fn()

    afterEach(() => {
      mocked(next).mockClear()
    })

    it('should call next() if the target is not changed', async () => {
      await $form.verifyBeforeLeaving(next)

      expect(next).toHaveBeenCalled()
    })
    it('should call next() if submit is in progress', async () => {
      $form.watch(() => true)
      // 「submit 中」を再現させるため終わらない Promise を引数として `$form.submit` を実行しておく
      // noinspection ES6MissingAwait
      $form.submit(() => new Promise(noop))

      await $form.verifyBeforeLeaving(next)

      expect(next).toHaveBeenCalled()
    })
    it('should call next() if the user allowed', async () => {
      mocked($confirm.show).mockResolvedValueOnce(true)
      $form.watch(() => true)

      await $form.verifyBeforeLeaving(next)

      await flushPromises()
      expect(next).toHaveBeenCalled()
    })
    it('should not call next() if the user canceled', async () => {
      mocked($confirm.show).mockResolvedValueOnce(false)
      $form.watch(() => true)

      await $form.verifyBeforeLeaving(next)

      expect(next).not.toHaveBeenCalled()
    })
  })
})
