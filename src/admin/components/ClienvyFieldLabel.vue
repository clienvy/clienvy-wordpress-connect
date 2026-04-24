<template>
  <div class="clienvy-field-label-wrapper">
    <label v-if="label" class="clienvy-field-label">{{ label }}</label>
    <slot/>
  </div>
</template>

<script setup>
defineProps({
  label: {type: String, default: ''},
});
</script>

<style scoped lang="scss">
.clienvy-field-label-wrapper {
  display: flex;
  flex-direction: column;
  gap: var(--clienvy-form-field-label-spacing);

  .clienvy-field-label {
    display: inline-flex;
    width: fit-content;
    font-family: var(--clienvy-text-font-family);
    font-size: var(--clienvy-form-label-font-size);
    font-weight: var(--clienvy-form-label-font-weight);
    color: var(--clienvy-form-label-color);
    transition: color 0.2s;
  }
}
</style>
