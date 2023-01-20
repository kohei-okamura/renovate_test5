<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <div data-z-data-card-item :class="$style.root" v-bind="data.attrs">
    <div :class="$style.icon">
      <v-icon v-if="props.icon">{{ props.icon }}</v-icon>
    </div>
    <div :class="$style.content">
      <div class="text-caption" :class="$style.label">
        <slot name="label">{{ props.label }}</slot>
      </div>
      <div class="text-body-2 text--primary" :class="$style.value">
        <slot>{{ props.value }}</slot>
      </div>
    </div>
  </div>
</template>

<style lang="scss" module>
.root {
  display: flex;
  flex-flow: row nowrap;
  padding: 12px 16px;
}

.icon {
  height: 24px;
  margin-top: 4px;
  min-width: 24px;
  width: 24px;
}

.content {
  flex-grow: 1;

  .icon + & {
    margin-left: 24px;
  }
}

.label {
  line-height: 1rem;
}

.value {
  margin-top: 2px;

  :global(.v-chip) {
    margin-bottom: 4px;
    margin-right: 4px;
  }
}
</style>
