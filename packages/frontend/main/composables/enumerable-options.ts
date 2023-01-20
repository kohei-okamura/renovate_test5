/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Enumerable, EnumerableDef } from '@zinger/enums/lib/enum'
import { VSelectOption } from '~/models/vuetify'

export const enumerableOptions = <T extends EnumerableDef, U extends keyof T, V extends T[U]> (
  enumerable: Enumerable<T, U, V>
): VSelectOption<V>[] => {
  return enumerable.values.map(value => ({ text: enumerable.resolve(value), value }))
}
