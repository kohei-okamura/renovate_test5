<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <div
    data-z-form-card-item-set
    :class="[data.staticClass, $style.root]"
    v-bind="data.attrs"
  >
    <div v-if="!props.noIcon" :class="$style.icon">
      <v-icon v-if="props.icon">{{ props.icon }}</v-icon>
    </div>
    <div :class="[{ 'ml-4': !!props.label }, $style.content]">
      <div v-if="!!props.label" class="text-caption pt-2">{{ props.label }}</div>
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
import { defineFunctionalComponent } from '~/components/tools/define-functional-component'

export default defineFunctionalComponent({
  name: 'ZFormCardItem',
  functional: true,
  props: {
    icon: { type: String, default: '' },
    label: { type: String, default: '' },
    noIcon: { type: Boolean, default: false }
  }
})
</script>

<style lang="scss" module>
.root {
  display: flex;
  flex-flow: row nowrap;
  max-width: 100%;
  padding: 0 16px;
}

.icon {
  flex: 0 0 auto;
  padding: 16px 0 0;
  width: 32px;

  + .content {
    :global(.v-input__control) {
      min-height: 48px;
    }
  }
}

.content {
  flex: 1 1 auto;
  min-width: 0;
}
</style>
