/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Vue, { FunctionalComponentOptions } from 'vue'
import { RecordPropsDefinition } from 'vue/types/options'
import { ExtendedVue } from 'vue/types/vue'

type DefineFunctionalComponentOptions<Props> = FunctionalComponentOptions<Props, RecordPropsDefinition<Props>> & {
  filters?: Record<string, (x: any) => string>
}

type DefineFunctionalComponent = <Props = Record<string, any>> (
  options: DefineFunctionalComponentOptions<Props>
) => ExtendedVue<Vue, unknown, unknown, unknown, Props>

/**
 * 関数型コンポーネントを定義する.
 */
export const defineFunctionalComponent: DefineFunctionalComponent = options => options as any
