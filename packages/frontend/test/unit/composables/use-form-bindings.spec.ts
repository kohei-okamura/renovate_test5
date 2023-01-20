/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, SetupContext } from '@nuxtjs/composition-api'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { FormProps, useFormBindings } from '~/composables/use-form-bindings'
import { usePlugins } from '~/composables/use-plugins'
import { SnackbarService } from '~/services/snackbar-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

type Form = {
  text: string
}

describe('composables/use-form-bindings', () => {
  setupComposableTest()

  const context = createMock<SetupContext>()
  const text = 'foo bar baz'
  const value: Form = { text }
  const props: FormProps<Form> = reactive({
    errors: {},
    progress: false,
    value
  })
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const plugins = createMockedPlugins({
    $form,
    $snackbar
  })

  beforeAll(() => {
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('form', () => {
    it('should initialized with props.value', () => {
      const { form } = useFormBindings<Form>(props, context)
      expect(form).toStrictEqual({
        text
      })
    })

    it('should initialized with options.init', () => {
      const { form } = useFormBindings<Form>(props, context, {
        init: x => ({
          text: x.text?.toUpperCase() ?? ''
        })
      })
      expect(form).toStrictEqual({
        text: text.toUpperCase()
      })
    })
  })

  describe('observer', () => {
    it('should be a ref to undefined', () => {
      const { observer } = useFormBindings<Form>(props, context)
      expect(observer).toBeRefTo(undefined)
    })
  })

  describe('submit', () => {
    beforeEach(() => {
      jest.spyOn(context, 'emit').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked(context.emit).mockReset()
      mocked($snackbar.error).mockReset()
    })

    it('should not emit when validation failed', async () => {
      const { observer, submit } = useFormBindings<Form>(props, context)
      const mockObserver = createMock<ValidationObserverInstance>()
      observer.value = createMock<ValidationObserverInstance>({
        observers: [],
        refs: { x: { ...mockObserver.refs.x } },
        fields: { x: { ...mockObserver.fields.x, invalid: true } }
      })
      jest.spyOn(observer.value, 'validate').mockResolvedValue(false)

      await submit()

      expect(context.emit).not.toHaveBeenCalled()
    })

    it('should emit input event when validation passed', async () => {
      const { form, observer, submit } = useFormBindings<Form>(props, context)
      observer.value = createMock<ValidationObserverInstance>()
      jest.spyOn(observer.value, 'validate').mockResolvedValue(true)

      await submit()

      expect(context.emit).toHaveBeenCalledTimes(1)
      expect(context.emit).toHaveBeenCalledWith('submit', form, observer.value)
      mocked(observer.value.validate).mockReset()
    })

    it('should display error message when submit', async () => {
      const { observer, submit } = useFormBindings<Form>(props, context)
      observer.value = createMock<ValidationObserverInstance>()
      jest.spyOn(observer.value, 'validate').mockResolvedValue(false)
      observer.value = {
        ...observer.value,
        observers: [],
        refs: { x: { ...observer.value.refs.x } },
        fields: { x: { ...observer.value.fields.x, invalid: true } }
      }

      await submit()

      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
    })
  })
})
