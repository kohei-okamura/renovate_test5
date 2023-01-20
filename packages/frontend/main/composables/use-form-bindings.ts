/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, Ref, SetupContext, toRefs, UnwrapRef, watch } from '@nuxtjs/composition-api'
import { identity } from '@zinger/helpers'
import clonedeep from 'lodash.clonedeep'
import { usePlugins } from '~/composables/use-plugins'
import { observerRef } from '~/support/reactive'
import { ValidationObserverInstance } from '~/support/validation/types'

type Form = Record<string, unknown>

export type FormProps<T extends Form> = Readonly<{
  errors: Record<string, string[]>
  progress: boolean
  value: Partial<T>
}>

type CreateFormBindingsOptions<T extends Form, R extends Form> = {
  init?: (form: Partial<T>) => Partial<T>
  processOutput?: (form: UnwrapRef<Partial<T>>) => Partial<R>
  resetValidatorOnReset?: boolean
}

export const getFormPropsOptions = () => ({
  errors: { type: Object, required: true },
  progress: { type: Boolean, required: true },
  value: { type: Object, required: true }
})

type FormBindings<T extends Form> = {
  form: UnwrapRef<Partial<T>>
  observer: Ref<ValidationObserverInstance | undefined>
  submit: () => Promise<void>
}

export const useFormBindings = <T extends Form, R extends Form = T> (
  props: FormProps<T>,
  context: SetupContext,
  options: CreateFormBindingsOptions<T, R> = {}
): FormBindings<T> => {
  const { $form, $snackbar } = usePlugins()

  // TODO: cloneが修正されたらclonedeepを置き換える
  const value = clonedeep(props.value)
  const init = options.init ?? (() => ({}))
  const form = reactive({
    ...value,
    ...init(value)
  })
  const updateForm = (data: Partial<T>) => {
    const newValue = clonedeep(data)
    // `reactive` の戻り値に `Object.assign` すると型エラーになるので `as` で握りつぶす.
    Object.assign(form as Record<string, any>, {
      ...newValue,
      ...init(newValue)
    })
  }
  const ref = observerRef()

  const useValidationErrorFeedback = () => {
    const observer = ref.value
    const getObservers = (observer: ValidationObserverInstance): ValidationObserverInstance[] => [
      observer,
      ...observer.observers.flatMap(observer => getObservers(observer))
    ]
    if (observer) {
      const observers = getObservers(observer)
      const error = observers
        .flatMap(observer => Object.keys(observer.refs).map(key => ({
          field: observer.fields[key],
          ref: observer.refs[key]
        })))
        .find(({ field }) => field.invalid)
      if (error) {
        $snackbar.error('正しく入力されていない項目があります。入力内容をご確認ください。')
        error.ref.$el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    }
  }

  const submit = async () => {
    const observer = ref.value
    if (await observer?.validate()) {
      const processOutput = options.processOutput ?? identity
      context.emit('submit', processOutput(form), observer)
      if (options.resetValidatorOnReset ?? false) {
        observer?.reset()
      }
    } else {
      useValidationErrorFeedback()
    }
  }

  $form.watch(() => ref.value?.flags.changed ?? false)
  const propRefs = toRefs(props)
  watch(propRefs.errors, errors => {
    ref.value?.setErrors(errors)
    // TODO setErrorsが非同期でnextTickでもうまくいかなかったのでsetTimeoutを使用
    setTimeout(useValidationErrorFeedback, 50)
  })
  watch(propRefs.value, value => updateForm(value))

  return {
    form,
    observer: ref,
    submit
  }
}
