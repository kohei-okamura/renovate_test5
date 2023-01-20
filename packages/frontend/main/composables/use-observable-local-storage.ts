/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, Ref, WritableComputedRef } from '@nuxtjs/composition-api'
import { UseStorageOptions, StorageSerializers, useLocalStorage } from '@vueuse/core'
import { MaybeRef } from '@vueuse/shared'

type AcceptableType = keyof typeof StorageSerializers
type AvailableKeys = 'developer-mode'

export const useObservableLocalStorage = <T> (
  key: AvailableKeys,
  initialValue: MaybeRef<T>,
  options?: UseStorageOptions<T>
): WritableComputedRef<T> => {
  // TODO: とりあえずそんなに使う予定はないので雑に判定
  const getType = (value: any): AcceptableType => {
    const type = typeof value
    return (Object.keys(StorageSerializers).find(x => x === type) ?? 'object') as AcceptableType
  }

  // FIXME: 下記のエラーがどうしても解消できない（型定義では `value` はあるはず）ので `as any` でなんとかする.
  // Property 'value' is missing in type 'Omit<Ref<T>, "value">' but required in type 'Ref<T>'.
  const item: Ref<T> = useLocalStorage(key, initialValue, options) as any

  return computed({
    get: () => item.value,
    set: newVal => {
      const serializer = StorageSerializers[getType(newVal)] ?? JSON.stringify
      const newValue = serializer.write(newVal)
      const event = new StorageEvent('storage', { key, newValue })
      window.dispatchEvent(event)
    }
  })
}
