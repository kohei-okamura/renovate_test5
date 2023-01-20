/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ReadonlyRefs, Refs, toReadonlyRefs } from '~/support/reactive'

export type StateProvider<T> = {
  readonly state: T
}

type R = Record<string, unknown>

export type Store<State extends R, Actions extends R, Getters extends R> = Actions
  & StateProvider<ReadonlyRefs<State> & ReadonlyRefs<Getters>>

type CreateStoreParams<State extends R, Actions extends R, Getters extends R> = {
  state: State
  getters?: Refs<Getters>
  actions: Actions
}

export function createStore<State extends R, Actions extends R, Getters extends R> (
  params: CreateStoreParams<State, Actions, Getters>
): Store<State, Actions, Getters> {
  const getters = params.getters ?? {}
  const state = {
    ...toReadonlyRefs(params.state),
    ...getters as ReadonlyRefs<Getters>
  }
  return {
    ...params.actions,
    state
  }
}
