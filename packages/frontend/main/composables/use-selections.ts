/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ref, Ref, watch } from '@nuxtjs/composition-api'

export const useSelections = (keys: Ref<string[]>) => {
  const selections: Ref<Record<string, boolean>> = ref({})
  const selectedKeys = computed(() => {
    const xs = selections.value
    return keys.value.filter(key => xs[key])
  })
  const selectedCount = computed(() => selectedKeys.value.length)
  const isSelected = computed(() => {
    const xs = selections.value
    return keys.value.some(key => xs[key])
  })
  const isFilled = computed(() => {
    const xs = selections.value
    return keys.value.every(key => xs[key])
  })
  const isIndeterminate = computed(() => isSelected.value && !isFilled.value)
  const setSelections = (value: boolean) => keys.value.forEach(key => {
    selections.value[key] = value
  })
  watch(
    keys,
    keys => {
      const currentSelections = selections.value
      selections.value = Object.fromEntries(keys.map(key => [key, currentSelections[key] ?? false]))
    },
    { immediate: true }
  )
  return {
    selections,
    selectedKeys,
    selectedCount,
    isSelected,
    isFilled,
    isIndeterminate,
    setSelections
  }
}
