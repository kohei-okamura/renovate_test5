<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <draggable
    animation="0"
    data-draggable
    drag-class=".sortable-tree-drag"
    draggable=".v-treeview-node"
    :class="[{ 'sortable-root-items': root }, $style.root]"
    :clone="cloneElement"
    :disabled="!isSortable"
    :group="group"
    :value="tree"
    @end="onDragEnd"
    @input="onInputDraggable"
    @start="onDragStart"
  >
    <div
      v-for="node in tree"
      :key="node.item[itemKey]"
      class="v-treeview-node"
      :class="{ 'v-treeview-node--leaf': hasParent(node) }"
    >
      <div :class="['v-treeview-node__root', { 'single': isSingle(node) }]">
        <v-icon v-if="hasChildren(node)" :class="{ 'v-treeview-node__toggle': !isOpened(node) }" @click="toggle(node)">
          {{ arrowIcon }}
        </v-icon>
        <div class="v-treeview-node__content">
          <slot name="prepend" :item="node.item"></slot>
          <div class="v-treeview-node__label">
            <span class="v-treeview-node__label-wrapper" @click="hasChildren(node) && toggle(node)">
              <slot name="label" :item="node.item">{{ node.item[itemName] }}</slot></span>
          </div>
          <slot name="append" :item="node.item"></slot>
        </div>
      </div>
      <v-expand-transition>
        <div v-if="isOpened(node)" class="v-treeview-node__children">
          <z-sortable-tree-items
            data-sortable-tree-items
            group="sortable-tree-children"
            :arrow-icon="arrowIcon"
            :is-sortable="isSortable"
            :item-key="itemKey"
            :item-name="itemName"
            :open="opened"
            :parent-key="parentKey"
            :value="node.children"
            @drag:end="onDragEnd"
            @drag:start="onDragStart"
            @input="onInput(node, $event)"
            @update="onUpdate"
          >
            <template #append="{ item }">
              <slot name="append" :item="item"></slot>
            </template>
            <template #prepend="{ item }">
              <slot name="prepend" :item="item"></slot>
            </template>
            <template #label="{ item }">
              <slot name="label" :item="item"></slot>
            </template>
          </z-sortable-tree-items>
        </div>
      </v-expand-transition>
    </div>
  </draggable>
</template>

<script lang="ts">
import { mdiMenuDown } from '@mdi/js'
import { computed, defineComponent, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import Vue from 'vue'
import Draggable from 'vuedraggable'
import { Tree, TreeNode } from '~/models/tree'
import { updateReactiveArray } from '~/support/reactive'

type Item = { [key: string]: any }

type Props = Readonly<{
  arrowIcon: string
  group: string | undefined
  isSortable: boolean
  itemName: keyof Item
  itemKey: keyof Item
  parentKey: keyof Item | undefined
  open: number[] | string[]
  root: boolean
  value: Tree<Item>
}>

export default defineComponent<Props>({
  name: 'ZSortableTreeItems',
  components: {
    Draggable
  },
  props: {
    arrowIcon: { type: String, default: mdiMenuDown },
    group: { type: String, default: 'sortable-tree-root' },
    isSortable: { type: Boolean, default: false },
    itemName: { type: String, default: 'name' },
    itemKey: { type: String, default: 'id' },
    parentKey: { type: String, default: undefined },
    open: { type: Array, required: true },
    root: { type: Boolean, default: false },
    value: { type: Array, required: true }
  },
  setup (props, context) {
    const state = reactive({
      opened: [...props.open],
      tree: [...props.value]
    })
    const propRefs = toRefs(props)
    watch(propRefs.value, value => updateReactiveArray(state.tree, value))
    watch(propRefs.open, value => updateReactiveArray(state.opened, value))
    const isOpened = computed(() => (node: TreeNode<Item>) => state.opened.includes(node.item[props.itemKey]))
    /* istanbul ignore next */
    const cloneElement = (x: unknown) => x
    const hasChildren = (node: TreeNode<Item>) => Array.isArray(node?.children) && node.children.length > 0
    const hasParent = (node: TreeNode<Item>) => !!props.parentKey && !!node?.item[props.parentKey]
    const isSingle = (node: TreeNode<Item>) => !hasParent(node) && !hasChildren(node)
    const onDragEnd = () => context.emit('drag:end')
    const onDragStart = () => context.emit('drag:start')
    const onUpdate = (args: { item: Item, children: Tree<Item> }) => {
      context.emit('update', args)
    }
    const onInput = (node: TreeNode<Item>, children: TreeNode<Item>[]) => {
      Vue.set(node, 'children', [...children])
      onUpdate({ item: node.item, children })
    }
    const onInputDraggable = (treeItems: Tree<Item>) => {
      updateReactiveArray(state.tree, treeItems)
      context.emit('input', state.tree)
    }
    const toggle = (node: TreeNode<Item>) => {
      const key = node.item[props.itemKey]
      const index = state.opened.indexOf(key)
      index === -1 ? state.opened.push(key) : state.opened.splice(index, 1)
    }
    return {
      ...toRefs(state),
      cloneElement,
      hasChildren,
      hasParent,
      isOpened,
      isSingle,
      onDragEnd,
      onDragStart,
      onInput,
      onInputDraggable,
      onUpdate,
      toggle
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global {
    .v-treeview-node {
      margin-left: 0;

      &.v-treeview-node--leaf {
        margin-left: 48px;
      }
    }

    .v-treeview-node__root {
      padding: 0 8px;

      &.single {
        margin-left: 24px;
      }
    }
  }
}
</style>
