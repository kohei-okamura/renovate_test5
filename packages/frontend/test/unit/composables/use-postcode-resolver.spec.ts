/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import Vue from 'vue'
import { usePostcodeResolver } from '~/composables/use-postcode-resolver'
import { Addr } from '~/models/addr'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/use-postcode-resolver', () => {
  let wrapper: Wrapper<Vue>
  let inputElement: HTMLInputElement

  beforeEach(() => {
    const divElement = document.createElement('div')
    if (document.body) {
      document.body.appendChild(divElement)
    }
    wrapper = setupComposableTest({
      attachTo: divElement,
      template: '<div><input type="text"></div>'
    })
    inputElement = wrapper.find('input').element as HTMLInputElement
  })

  afterEach(() => {
    wrapper.destroy()
  })

  it('should update form values with argument values when onPostcodeResolved is called', async () => {
    const form: Addr = {
      postcode: '060-8588',
      prefecture: Prefecture.hokkaido,
      city: '札幌市',
      street: '中央区北３条西６丁目',
      apartment: '北海道県庁'
    }
    const newAddr: Addr = {
      postcode: '163-8001',
      prefecture: Prefecture.tokyo,
      city: '新宿区',
      street: '西新宿２丁目８−１',
      apartment: '東京都庁'
    }
    const { onPostcodeResolved, streetInput } = usePostcodeResolver(form)
    streetInput.value = inputElement
    expect(document.activeElement).not.toBe(inputElement)

    await onPostcodeResolved(newAddr)

    expect(form.prefecture).toBe(newAddr.prefecture)
    expect(form.city).toBe(newAddr.city)
    expect(form.street).toBe(newAddr.street)
    expect(form.apartment).not.toBe(newAddr.apartment)
    expect(document.activeElement).toBe(inputElement)
  })
})
