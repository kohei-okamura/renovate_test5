<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template functional>
  <validation-provider
    v-slot="{ errors }"
    data-z-form-card-item
    tag="div"
    :class="[$style.root, data.class, data.staticClass]"
    :custom-messages="props.customMessages"
    :rules="props.rules"
    v-bind="data.attrs"
  >
    <slot :errors="errors"></slot>
  </validation-provider>
</template>

<style lang="scss" module>
.root {
  flex: 1 1 50%;
  margin: 0 0 0 8px;
  max-width: 100%;
}
</style>
