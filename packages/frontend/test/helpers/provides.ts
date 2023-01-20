/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey } from '@nuxtjs/composition-api'
import { MountOptions } from '@vue/test-utils'
import Vue from 'vue'

type ProvideDef<T> = [InjectionKey<T>, T]

type Provide = {
  provide: MountOptions<Vue>['provide']
}

type Provides = {
  <A> (a: ProvideDef<A>): Provide
  <A, B> (a: ProvideDef<A>, b: ProvideDef<B>): Provide
  <A, B, C> (a: ProvideDef<A>, b: ProvideDef<B>, c: ProvideDef<C>): Provide
  <A, B, C, D> (a: ProvideDef<A>, b: ProvideDef<B>, c: ProvideDef<C>, d: ProvideDef<D>): Provide
  <A, B, C, D, E> (a: ProvideDef<A>, b: ProvideDef<B>, c: ProvideDef<C>, d: ProvideDef<D>, e: ProvideDef<E>): Provide
  <A, B, C, D, E, F> (
    a: ProvideDef<A>,
    b: ProvideDef<B>,
    c: ProvideDef<C>,
    d: ProvideDef<D>,
    e: ProvideDef<E>,
    f: ProvideDef<F>
  ): Provide
  <A, B, C, D, E, F, G> (
    a: ProvideDef<A>,
    b: ProvideDef<B>,
    c: ProvideDef<C>,
    d: ProvideDef<D>,
    e: ProvideDef<E>,
    f: ProvideDef<F>,
    g: ProvideDef<G>
  ): Provide
  <A, B, C, D, E, F, G, H> (
    a: ProvideDef<A>,
    b: ProvideDef<B>,
    c: ProvideDef<C>,
    d: ProvideDef<D>,
    e: ProvideDef<E>,
    f: ProvideDef<F>,
    g: ProvideDef<G>,
    h: ProvideDef<H>
  ): Provide
  <A, B, C, D, E, F, G, H, I> (
    a: ProvideDef<A>,
    b: ProvideDef<B>,
    c: ProvideDef<C>,
    d: ProvideDef<D>,
    e: ProvideDef<E>,
    f: ProvideDef<F>,
    g: ProvideDef<G>,
    h: ProvideDef<H>,
    i: ProvideDef<I>
  ): Provide
}

export const provides: Provides = (...defs: ProvideDef<any>[]): Provide => ({
  provide: Object.fromEntries(defs)
})
