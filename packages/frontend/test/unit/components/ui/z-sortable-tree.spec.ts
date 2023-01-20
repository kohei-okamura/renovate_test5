/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue, { ComponentOptions } from 'vue'
import ZSortableTree from '~/components/ui/z-sortable-tree.vue'
import { createTree } from '~/models/tree'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-sortable-tree.vue', () => {
  const stubTree = createTree(createOfficeGroupStubs(), 'parentOfficeGroupId')
  const parentTreeId = 30
  const { mount } = setupComponentTest()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: ComponentOptions<Vue> = {}): void {
    const propsData = {
      value: stubTree,
      open: [parentTreeId]
    }
    wrapper = mount(ZSortableTree, {
      propsData,
      ...options
    })
  }

  function unmountComponent (): void {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should update the style(class) state when drag:start event occurred', async () => {
    mountComponent()
    const treeview = wrapper.find('.v-treeview')
    expect(treeview.classes()).toContain('v-treeview--hoverable')

    await wrapper.find('[data-sortable-tree-items]').vm.$emit('drag:start')

    expect(treeview.classes()).not.toContain('v-treeview--hoverable')
    unmountComponent()
  })

  it('should update the style(class) to false when drag:end event occurred', async () => {
    mountComponent()
    const treeview = wrapper.find('.v-treeview')
    expect(treeview.classes()).toContain('v-treeview--hoverable')

    await wrapper.find('[data-sortable-tree-items]').vm.$emit('drag:start')

    expect(treeview.classes()).not.toContain('v-treeview--hoverable')

    await wrapper.find('[data-sortable-tree-items]').vm.$emit('drag:end')

    expect(treeview.classes()).toContain('v-treeview--hoverable')
    unmountComponent()
  })

  it('should update the content when props.value is changed', async () => {
    const propsData = {
      value: [],
      open: []
    }
    mountComponent({ propsData })
    const value = stubTree.filter(v => v.item.id === parentTreeId)

    await wrapper.setProps({ value })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be update "opened" when props.open is changed', async () => {
    const propsData = {
      value: stubTree,
      open: []
    }
    mountComponent({ propsData })
    const open = [10, 20]

    await wrapper.setProps({ open })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should emit an event "update" when update event occurred in tree-items', async () => {
    mountComponent()
    const { item, children } = stubTree.filter(v => v.item.id === parentTreeId)[0]

    await wrapper.find('[data-sortable-tree-items]').vm.$emit('update', {
      item,
      children
    })

    const emitted = wrapper.emitted('update')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual({
      item,
      children
    })
    unmountComponent()
  })

  it('should emit an event "update" when input event occurred in tree-items', async () => {
    mountComponent()
    const { children } = stubTree.filter(v => v.item.id === parentTreeId)[0]

    await wrapper.find('[data-sortable-tree-items]').vm.$emit('input', children)

    const emitted = wrapper.emitted('update')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual({
      item: undefined,
      children
    })
    unmountComponent()
  })
})
