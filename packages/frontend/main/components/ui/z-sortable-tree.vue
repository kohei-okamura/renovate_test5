<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div class="text-body-2" :class="$style.root">
    <div class="v-treeview v-treeview--dense theme--light" :class="{ 'v-treeview--hoverable': !dragging }">
      <z-sortable-tree-items
        data-sortable-tree-items
        :arrow-icon="arrowIcon"
        :class="{ [$style.dragEnabled]: isSortable }"
        :is-sortable="isSortable"
        :item-key="itemKey"
        :item-name="itemName"
        :open="opened"
        :parent-key="parentKey"
        :root="true"
        :value="tree"
        @drag:end="onDragEnd"
        @drag:start="onDragStart"
        @input="onInput"
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
  </div>
</template>

<script lang="ts">
import { mdiMenuDown } from '@mdi/js'
import { defineComponent, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Tree, TreeNode } from '~/models/tree'
import { updateReactiveArray } from '~/support/reactive'

type Item = { [key: string]: any }

type Props = Readonly<{
  arrowIcon: string
  isSortable: boolean
  itemName: keyof Item
  itemKey: keyof Item
  parentKey: keyof Item | undefined
  open: number[] | string[]
  value: Tree<Item>
}>

export default defineComponent<Props>({
  name: 'ZSortableTree',
  props: {
    arrowIcon: { type: String, default: mdiMenuDown },
    isSortable: { type: Boolean, default: false },
    itemName: { type: String, default: 'name' },
    itemKey: { type: String, default: 'id' },
    parentKey: { type: String, default: undefined },
    open: { type: Array, default: () => [] },
    value: { type: Array, default: () => [] }
  },
  setup (props, context) {
    const propRefs = toRefs(props)
    const state = reactive({
      dragging: false,
      opened: [...props.open],
      tree: [...props.value]
    })
    watch(propRefs.value, value => updateReactiveArray(state.tree, value))
    watch(propRefs.open, value => updateReactiveArray(state.opened, value))
    const onDragEnd = () => {
      state.dragging = false
    }
    const onDragStart = () => {
      state.dragging = true
    }
    const onInput = (children: TreeNode<Item>[]) => {
      onUpdate({ item: undefined, children })
    }
    const onUpdate = (args: { item?: Item, children: Tree<Item> }) => {
      context.emit('update', args)
    }
    return {
      ...toRefs(state),
      onDragEnd,
      onDragStart,
      onInput,
      onUpdate
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/components/VTreeview/VTreeview';

.root {
  :global {
    .sortable-ghost {
      opacity: 0.2;
    }

    .sortable-root-items > .v-treeview-node {
      margin-left: 0;
    }

    .v-treeview-node__label-wrapper {
      cursor: pointer;
      font-size: 14px;
    }

    .v-treeview-node--leaf .v-treeview-node__label-wrapper {
      cursor: auto;
    }
  }
}

.dragEnabled > :global(.v-treeview-node) {
  cursor: move;
}
</style>
