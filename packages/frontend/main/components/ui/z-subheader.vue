<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <v-subheader data-z-subheader :class="[$style.root, data.staticClass]" v-bind="data.attrs">
    <div :class="$style.inner">
      <slot></slot>
    </div>
  </v-subheader>
</template>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/variables';

.root:global(.v-subheader) {
  margin: 0;
  padding: 0;
}

.inner {
  font-size: map-deep-get($headings, 'h6', 'size') !important;
  padding: 0 8px;
}
</style>
