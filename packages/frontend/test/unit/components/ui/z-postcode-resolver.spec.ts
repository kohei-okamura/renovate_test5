/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZPostcodeResolver from '~/components/ui/z-postcode-resolver.vue'
import { HttpStatusCode } from '~/models/http-status-code'
import { Postcode } from '~/models/postcode'
import { Plugins } from '~/plugins'
import { SnackbarService } from '~/services/snackbar-service'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { setData } from '~~/test/helpers/set-data'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-postcode-resolver.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('postcode')
  const $snackbar = createMock<SnackbarService>()
  const mocks: Partial<Plugins> = {
    $api,
    $snackbar
  }
  const propsData = {
    postcode: '164-0011'
  }
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZPostcodeResolver, {
      ...options,
      mocks,
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('button', () => {
    let button: Wrapper<any>

    beforeAll(() => {
      mountComponent()
      button = wrapper.find('[data-postcode-resolver-button]')
    })

    afterAll(() => {
      unmountComponent()
    })

    it.each([
      ['164-0011'],
      ['1640011']
    ])('should not be disabled when the postcode is valid (%s)', async postcode => {
      await setProps(wrapper, { postcode })
      expect(button).not.toBeDisabled()
    })

    it.each([
      [''],
      ['ABC'],
      ['ABC-1234']
    ])('should be disabled when the postcode is invalid (%s)', async postcode => {
      await setProps(wrapper, { postcode })
      expect(button).toBeDisabled()
    })
  })

  describe('onClick', () => {
    beforeEach(() => {
      mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    describe('when api responses single data', () => {
      beforeEach(() => {
        jest.spyOn($api.postcode, 'get').mockResolvedValue([{
          prefecture_jis_code: '13',
          city_jis_code: '13114',
          zip_code: '1640011',
          prefecture_name_kana: 'ﾄｳｷｮｳﾄ',
          city_name_kana: 'ﾅｶﾉｸ',
          town_name_kana: 'ﾁｭｳｵｳ',
          prefecture_name: '東京都',
          city_name: '中野区',
          town_name: '中央'
        }])
      })

      afterEach(() => {
        mocked($api.postcode.get).mockReset()
      })

      it('should emit update event', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))

        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(1)
        expect(emitted[0]).toHaveLength(1)
        expect(emitted[0][0]).toStrictEqual({
          postcode: '1640011',
          prefecture: Prefecture.tokyo,
          city: '中野区',
          street: '中央',
          apartment: ''
        })
      })

      it('should not display the dialog', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })
    })

    describe('when api responses multiple data', () => {
      beforeEach(() => {
        jest.spyOn($api.postcode, 'get').mockResolvedValue([
          {
            prefecture_jis_code: '05',
            city_jis_code: '05212',
            zip_code: '0191834',
            prefecture_name_kana: 'ｱｷﾀｹﾝ',
            city_name_kana: 'ﾀﾞｲｾﾝｼ',
            town_name_kana: 'ﾅﾝｶﾞｲｱｹﾞﾂﾁﾔﾏ',
            prefecture_name: '秋田県',
            city_name: '大仙市',
            town_name: '南外揚土山'
          },
          {
            prefecture_jis_code: '05',
            city_jis_code: '05212',
            zip_code: '0191834',
            prefecture_name_kana: 'ｱｷﾀｹﾝ',
            city_name_kana: 'ﾀﾞｲｾﾝｼ',
            town_name_kana: 'ﾅﾝｶﾞｲｷﾀﾀﾞｸﾛｾ',
            prefecture_name: '秋田県',
            city_name: '大仙市',
            town_name: '南外北田黒瀬'
          }
        ])
      })

      afterEach(() => {
        mocked($api.postcode.get).mockReset()
      })

      it('should not emit update', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(0)
      })

      it('should display the dialog', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBe('true')
      })
    })

    describe('when api responses no data', () => {
      beforeEach(() => {
        jest.spyOn($api.postcode, 'get').mockResolvedValue([])
        jest.spyOn($snackbar, 'warning').mockReturnValue()
      })

      afterEach(() => {
        mocked($api.postcode.get).mockReset()
        mocked($snackbar.warning).mockReset()
      })

      it('should not emit update', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(0)
      })

      it('should not display the dialog', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })

      it('should display snackbar', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        expect($snackbar.warning).toHaveBeenCalled()
        expect($snackbar.warning).toHaveBeenCalledWith('郵便番号に対応する住所が見つかりませんでした。')
      })
    })

    describe('when api throws an error(403 Forbidden)', () => {
      beforeEach(() => {
        jest.spyOn($api.postcode, 'get').mockRejectedValue(createAxiosError(HttpStatusCode.Forbidden))
        jest.spyOn($snackbar, 'error').mockReturnValue()
      })

      afterEach(() => {
        mocked($api.postcode.get).mockReset()
        mocked($snackbar.error).mockReset()
      })

      it('should not emit update', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(0)
      })

      it('should not display the dialog', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })

      it('should display snackbar', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        expect($snackbar.error).toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledWith('住所の取得に失敗しました。')
      })
    })

    describe('when api throws an error(404 NotFound)', () => {
      beforeEach(() => {
        jest.spyOn($api.postcode, 'get').mockRejectedValue(createAxiosError(HttpStatusCode.NotFound))
        jest.spyOn($snackbar, 'error').mockReturnValue()
      })

      afterEach(() => {
        mocked($api.postcode.get).mockReset()
        mocked($snackbar.error).mockReset()
      })

      it('should not emit update', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(0)
      })

      it('should not display the dialog', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })

      it('should display snackbar', async () => {
        await click(() => wrapper.find('[data-postcode-resolver-button]'))
        expect($snackbar.error).toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledWith('住所の取得に失敗しました。')
      })
    })
  })

  describe('dialog', () => {
    beforeEach(async () => {
      const postcodes: Postcode[] = [
        {
          prefecture_jis_code: '27',
          city_jis_code: '27209',
          zip_code: '5700043',
          prefecture_name_kana: 'ｵｵｻｶﾌ',
          city_name_kana: 'ﾓﾘｸﾞﾁｼ',
          town_name_kana: 'ﾀｶｾｷｭｳｵｵｴﾀﾞ',
          prefecture_name: '大阪府',
          city_name: '守口市',
          town_name: '高瀬旧大枝'
        },
        {
          prefecture_jis_code: '27',
          city_jis_code: '27209',
          zip_code: '5700043',
          prefecture_name_kana: 'ｵｵｻｶﾌ',
          city_name_kana: 'ﾓﾘｸﾞﾁｼ',
          town_name_kana: 'ﾀｶｾｷｭｳｾｷﾞ',
          prefecture_name: '大阪府',
          city_name: '守口市',
          town_name: '高瀬旧世木'
        },
        {
          prefecture_jis_code: '27',
          city_jis_code: '27209',
          zip_code: '5700043',
          prefecture_name_kana: 'ｵｵｻｶﾌ',
          city_name_kana: 'ﾓﾘｸﾞﾁｼ',
          town_name_kana: 'ﾀｶｾｷｭｳﾊﾞﾊﾞ',
          prefecture_name: '大阪府',
          city_name: '守口市',
          town_name: '高瀬旧馬場'
        },
        {
          prefecture_jis_code: '27',
          city_jis_code: '27209',
          zip_code: '5700043',
          prefecture_name_kana: 'ｵｵｻｶﾌ',
          city_name_kana: 'ﾓﾘｸﾞﾁｼ',
          town_name_kana: 'ﾐﾅﾐﾃﾗｶﾀﾋｶﾞｼﾄﾞｵﾘ',
          prefecture_name: '大阪府',
          city_name: '守口市',
          town_name: '南寺方東通'
        }
      ]
      const data = {
        dialog: true,
        postcodes
      }
      mountComponent()
      await setData(wrapper, { data })
    })

    describe('close button', () => {
      it('should close the dialog when click cancel', async () => {
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(dialog.attributes('value')).toBe('true')
        await wrapper.find('[data-postcode-resolver-cancel]').trigger('click')
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })
    })

    describe('submit button', () => {
      let button: Wrapper<any>

      beforeEach(() => {
        button = wrapper.find('[data-postcode-resolver-ok]')
      })

      it('should be disabled when postcode not selected', () => {
        expect(button).toBeDisabled()
      })

      it('should not be disabled when postcode selected', async () => {
        expect(button).toBeDisabled()
        await click(() => wrapper.find('.v-input--selection-controls__ripple'))
        expect(button).not.toBeDisabled()
      })

      it('should emit update event when clicked', async () => {
        await click(() => wrapper.find('.v-input--selection-controls__ripple'))
        await click(() => button)
        const emitted = wrapper.emitted('update') ?? []
        expect(emitted).toHaveLength(1)
        expect(emitted[0]).toHaveLength(1)
        expect(emitted[0][0]).toStrictEqual({
          postcode: '5700043',
          prefecture: Prefecture.osaka,
          city: '守口市',
          street: '高瀬旧大枝',
          apartment: ''
        })
      })

      it('should close the dialog when clicked', async () => {
        const dialog = wrapper.find('[data-postcode-resolver-dialog]')
        expect(dialog.attributes('value')).toBe('true')
        await click(() => wrapper.find('.v-input--selection-controls__ripple'))
        await click(() => button)
        expect(wrapper).toMatchSnapshot()
        expect(dialog.attributes('value')).toBeFalsy()
      })
    })
  })
})
