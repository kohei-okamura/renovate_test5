<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-date-picker ref="picker" v-model="syncedValue" v-bind="attrs">
    <slot></slot>
  </v-date-picker>
</template>

<script lang="ts">
import { computed, defineComponent, nextTick, watch, watchEffect } from '@nuxtjs/composition-api'
import { DayOfWeek } from '@zinger/enums/lib/day-of-week'
import { assert, pick } from '@zinger/helpers'
import { Vue } from 'vue/types/vue'
import { eraMonth, eraYear } from '~/composables/era-date'
import { DateLike, DateString, ISO_DATE_FORMAT, ISO_MONTH_FORMAT, OLDEST_DATE } from '~/models/date'
import { VDatePickerType } from '~/models/vuetify'
import { $datetime } from '~/services/datetime-service'
import { componentRef } from '~/support/reactive'

type Props = Readonly<{
  birthday: boolean
  dialog: boolean
  max: DateLike
  min: DateLike
  multiple: boolean
  type: VDatePickerType
  useJapaneseEra: boolean
  value: DateLike | DateLike[] | undefined
}>

export default defineComponent<Props>({
  name: 'ZDateField',
  props: {
    birthday: { type: Boolean, default: false },
    dialog: { type: Boolean, default: false },
    max: { type: [String, Object], default: () => $datetime.now.plus({ years: 10 }).toISODate() },
    min: { type: [String, Object], default: OLDEST_DATE },
    multiple: { type: Boolean, default: false },
    type: { type: String, default: 'date' },
    useJapaneseEra: { type: Boolean, default: true },
    value: { type: [Array, String, Object], default: undefined }
  },
  setup (props: Props, context) {
    const isValidProps = computed(() => {
      return props.multiple ? Array.isArray(props.value) : !Array.isArray(props.value)
    })
    watch(
      isValidProps,
      isValid => assert(
        isValid,
        'If props.multiple is true, then props.value must be an array.\n' +
        'Otherwise props.value must not be an array.\n' +
        `[multiple: ${props.multiple}, value is array: ${Array.isArray(props.value)}]`
      ),
      { immediate: true }
    )

    const emitFormat = computed(() => props.type === 'month' ? ISO_MONTH_FORMAT : ISO_DATE_FORMAT)
    const syncedValue = props.multiple
      ? computed({
        get: () => (props.value as DateLike[]).map(x => $datetime.parse(x).toISODate()),
        set: (values: string[]): void => {
          context.emit('input', values.map(x => $datetime.parse(x).toFormat(emitFormat.value)))
        }
      })
      : computed({
        get: () => $datetime.parse(props.value as DateLike | undefined)?.toISODate(),
        set: (value: string | undefined): void => {
          context.emit('input', $datetime.parse(value)?.toFormat(emitFormat.value))
        }
      })

    const picker = componentRef<Vue & { activePicker: Uppercase<VDatePickerType> }>()
    watchEffect(async () => {
      const { birthday, dialog } = props
      if (birthday && dialog) {
        await nextTick()
        const x = picker.value
        assert(x !== undefined, 'Undefined component ref: picker')
        x.activePicker = 'YEAR'
      }
    })

    /* istanbul ignore next */
    const yearFormat = (x: DateString) => `${eraYear(x)}/${$datetime.parse(x).toFormat('yyyy')}年`
    /* istanbul ignore next */
    const monthFormat = (x: DateString) => eraMonth(x)
    /* istanbul ignore next */
    const headerDateFormat = (x: DateString) => /^\d{4}-\d{2}$/.test(x) ? monthFormat(x) : yearFormat(x)
    /* istanbul ignore next */
    const titleDateFormat = monthFormat
    /* istanbul ignore next */
    const dayFormat = (date: string) => new Date(date).getDate()

    const formats = () => props.useJapaneseEra
      ? { headerDateFormat, titleDateFormat, yearFormat }
      : {}
    const attrs = computed(() => ({
      ...context.attrs,
      ...pick(props, ['min', 'max', 'multiple', 'type']),
      ...formats(),
      dayFormat,
      firstDayOfWeek: DayOfWeek.mon,
      locale: 'ja-JP',
      noTitle: true
    }))

    return {
      attrs,
      picker,
      syncedValue
    }
  }
})
</script>
