/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, Ref, SetupContext } from '@nuxtjs/composition-api'

type UseSyncedProp = <Props, Name extends string & keyof Props> (
  name: Name,
  props: Props,
  context: SetupContext,
  eventName?: string
) => Ref<Props[Name]>

export const useSyncedProp: UseSyncedProp = (name, props, context, eventName = `update:${name}`) => {
  return computed({
    get: () => props[name],
    set: (value: any) => context.emit(eventName, value)
  })
}
