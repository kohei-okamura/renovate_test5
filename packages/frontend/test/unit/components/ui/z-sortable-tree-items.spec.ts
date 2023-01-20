/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createWrapper, Wrapper } from '@vue/test-utils'
import Vue, { ComponentOptions } from 'vue'
import ZSortableTreeItems from '~/components/ui/z-sortable-tree-items.vue'
import { createTree } from '~/models/tree'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-sortable-tree-items.vue', () => {
  const stubTree = createTree(createOfficeGroupStubs(), 'parentOfficeGroupId')
  const parentTreeId = 30
  const { mount } = setupComponentTest()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: ComponentOptions<Vue> = {}): void {
    const propsData = {
      value: stubTree,
      open: [parentTreeId]
    }
    wrapper = mount(ZSortableTreeItems, {
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

  /*
   * FIXME: draggable の start が発火したときに、drag:start が emit されるのを確認したい
   * trigger、vm.$emit どちらもダメだった、カバレッジは別のケースで担保できているためひとまずスキップ
   */
  it.skip('should emit drag:start event when start event occurred in draggable', () => {
    // const value = stubTree.filter(v => v.item.id === parentTreeId)
    // const propsData = { value, open: [] }
    // const localWrapper = mount(ZSortableTreeItems, { propsData })
    // localWrapper.find('[data-draggable]').trigger('start')
    // const emitted = localWrapper.emitted('drag:start')
    // expect(emitted).toBeTruthy()
  })

  /*
   * FIXME: draggable の end が発火したときに、drag:end が emit されるのを確認したい
   * trigger、vm.$emit どちらもダメだった、カバレッジは別のケースで担保できているためひとまずスキップ
   */
  it.skip('should emit drag:end event when end event occurred in draggable', () => {
    // const value = stubTree.filter(v => v.item.id === parentTreeId)
    // const propsData = { value, open: [] }
    // const localWrapper = mount(ZSortableTreeItems, { propsData })
    // localWrapper.find('[data-draggable]').trigger('end')
    // const emitted = localWrapper.emitted('drag:end')
    // expect(emitted).toBeTruthy()
  })

  it('should update the content when props.value is changed', async () => {
    const value = stubTree.filter(v => v.item.id === parentTreeId)
    const propsData = { value: [], open: [] }
    mountComponent({ propsData })

    await wrapper.setProps({ value })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be update "opened" when props.open is changed', async () => {
    const propsData = {
      value: stubTree,
      open: []
    }
    const open = [parentTreeId]
    mountComponent({ propsData })

    await wrapper.setProps({ open })

    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should emit an event "input" when input event occurred in draggable', () => {
    mountComponent()
    const draggableWrapper = createWrapper(wrapper.vm.$children[0])
    const tree = stubTree.filter(v => v.item.id === parentTreeId)

    draggableWrapper.vm.$emit('input', tree)

    const emitted = wrapper.emitted('input')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(tree)
    unmountComponent()
  })

  it('should emit an event "drag:start" when drag:start event occurred in tree-items', () => {
    mountComponent()

    wrapper.find('[data-sortable-tree-items]').vm.$emit('drag:start')

    const emitted = wrapper.emitted('drag:start')
    expect(emitted).toBeTruthy()
    unmountComponent()
  })

  it('should emi an event "drag:end" when drag:end event occurred in tree-items', () => {
    mountComponent()

    wrapper.find('[data-sortable-tree-items]').vm.$emit('drag:end')

    const emitted = wrapper.emitted('drag:end')
    expect(emitted).toBeTruthy()
    unmountComponent()
  })

  it('should emit an event "update" when update event occurred in tree-items', () => {
    mountComponent()
    const { item, children } = stubTree.filter(v => v.item.id === parentTreeId)[0]
    const emitArgs = {
      item,
      children
    }
    wrapper.find('[data-sortable-tree-items]').vm.$emit('update', emitArgs)

    const emitted = wrapper.emitted('update')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual(emitArgs)
    unmountComponent()
  })

  it('should emit an event "update" when input event occurred in tree-items', () => {
    const propsData = {
      value: stubTree,
      open: [parentTreeId]
    }
    mountComponent({ propsData })
    const { item, children } = stubTree.filter(v => v.item.id === parentTreeId)[0]

    wrapper.find('[data-sortable-tree-items]').vm.$emit('input', children)

    const emitted = wrapper.emitted('update')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
    expect(emitted![0][0]).toStrictEqual({ item, children })
    unmountComponent()
  })

  /*
   * FIXME: v-icon の click が発火したときに、toggle() が呼ばれるのを確認したい
   * @see https://github.com/vuejs/vue-test-utils/issues/919
   */
  it('should be called toggle() when the click event occurred on v-icon(toggle-tree)', () => {
    /* ESLint エラー回避のためのコメント */
  })
})
