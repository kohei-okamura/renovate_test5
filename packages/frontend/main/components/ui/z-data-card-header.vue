<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <div data-z-data-card-header class="d-flex" :class="$style.root" v-bind="data.attrs">
    <slot></slot>
  </div>
</template>

<style lang="scss" module>
.root {
  margin: 0 0 -24px;
  padding: 12px 12px 0;
}
</style>
