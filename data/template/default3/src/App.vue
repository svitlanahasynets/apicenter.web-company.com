<template>
<div>
    <component :is="layout">
        <transition name="fade" mode="out-in">
        </transition>
    </component>
</div>
</template>

<script>
  const default_layout = "default";
  export default {
    computed: {
      layout() {
        return default_layout + '-layout';
      },
    },
  }
</script>

<style lang="scss">
  @import "assets/base.scss";
</style>
