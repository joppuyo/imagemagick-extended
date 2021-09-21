<template>
  <div class="ime-panel">
    <div class="ime-panel-title">{{title}}</div>
    <div class="ime-panel-content">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  props: ['title']
}
</script>