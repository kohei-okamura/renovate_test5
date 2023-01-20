<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div :class="classMap" v-bind="$attrs">
    <z-text-field
      readonly
      :value="textValue"
      v-bind="textFieldAttrs"
      @click.stop="open"
      @focus.stop="open"
    />
    <v-dialog v-model="dialog" data-z-date-field-dialog width="290px" @click:outside="cancel">
      <z-date-picker data-z-date-field-picker v-bind="pickerAttrs" @input="done">
        <v-spacer />
        <v-btn v-if="clearable" color="primary" data-z-date-field-button-clear text @click="clear">クリア</v-btn>
        <v-btn color="primary" data-z-date-field-button-cancel text @click="cancel">キャンセル</v-btn>
      </z-date-picker>
    </v-dialog>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { reactive } from '@vue/composition-api'
import { debounce, nonEmpty, pick } from '@zinger/helpers'
import { eraDate, eraMonth } from '~/composables/era-date'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike, READABLE_DATE_FORMAT, READABLE_MONTH_FORMAT } from '~/models/date'
import { VDatePickerType } from '~/models/vuetify'

type Props = Readonly<{
  birthday: boolean
  clearable: boolean
  disabled: boolean
  errorMessages: string[]
  label: string | undefined
  type: VDatePickerType
  useJapaneseEra: boolean
  value: DateLike | undefined
}>

export default defineComponent<Props>({
  name: 'ZDateField',
  props: {
    birthday: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    errorMessages: { type: Array, default: () => [] },
    label: { type: String, default: undefined },
    type: { type: String, default: 'date' },
    useJapaneseEra: { type: Boolean, default: true },
    value: { type: [String, Object], default: undefined }
  },
  setup (props: Props, context) {
    const { attrs } = context
    const { $datetime } = usePlugins()
    const state = reactive({
      dialog: false
    })

    const pickerAttrs = computed(() => ({
      ...pick(state, ['dialog']),
      ...pick(props, ['birthday', 'type', 'useJapaneseEra', 'value']),
      ...pick(attrs, ['min', 'max', 'picker-date'])
    }))
    const textFieldAttrs = computed(() => ({
      ...pick(props, ['errorMessages', 'disabled', 'label']),
      hideDetails: attrs['hide-details'] ?? false,
      prependIcon: attrs['prepend-icon']
    }))
    const classMap = computed(() => ({
      'z-date-field': true,
      'z-date-field--birthday': props.birthday
    }))
    const textValue = computed(() => {
      const value = props.value
      const isMonth = props.type === 'month'
      if (props.useJapaneseEra && nonEmpty(value)) {
        return isMonth ? eraMonth(value) : eraDate(value)
      } else {
        return $datetime.parse(value)?.toFormat(isMonth ? READABLE_MONTH_FORMAT : READABLE_DATE_FORMAT)
      }
    })

    // v-dialog の click-outside を迂回するためタイマーを用いる
    // TODO: setTimeout を使わないスマートな方法で実装したい
    const open = debounce({ wait: 10 }, () => {
      if (document.activeElement instanceof HTMLElement) {
        document.activeElement.blur()
      }
      state.dialog = true
    })
    const done = (value?: DateLike): void => {
      state.dialog = false
      context.emit('input', value)
      context.emit('blur')
    }
    const cancel = (): void => {
      state.dialog = false
      context.emit('blur')
    }
    const clear = (): void => done()
    return {
      ...toRefs(state),
      cancel,
      classMap,
      clear,
      done,
      open,
      pickerAttrs,
      textFieldAttrs,
      textValue
    }
  }
})
</script>
