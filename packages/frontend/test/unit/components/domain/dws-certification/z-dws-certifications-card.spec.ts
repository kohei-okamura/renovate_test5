/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDwsCertificationsCard from '~/components/domain/dws-certification/z-dws-certifications-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createDwsCertificationStubsForUser } from '~~/stubs/create-dws-certification-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-dws-certifications-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const user = createUserStub()
    const items = createDwsCertificationStubsForUser(user.id)
    const propsData = { items, user }
    wrapper = mount(ZDwsCertificationsCard, {
      ...provides(
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      propsData
    })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('pc layout should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('smart phone layout should be rendered correctly', async () => {
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs - 1 }, () => {
      expect(wrapper).toMatchSnapshot()
    })
  })

  // FIXME: できれば itemLink のケースを追加する（ z-data-table が関わるので後回し）
  it.skip('itemLink', async () => { /* ESLint エラー回避のためのコメント */ })
})
