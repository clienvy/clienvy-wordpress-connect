<template>
  <clienvy-field-label :label="label">
    <div class="clienvy-display-text">
      <span v-if="hasValue" class="clienvy-display-text-value">{{ modelValue }}</span>
      <span v-else class="clienvy-display-text-empty">-</span>
    </div>
  </clienvy-field-label>
</template>

<script setup>
import {computed} from 'vue';
import ClienvyFieldLabel from './ClienvyFieldLabel.vue';

const props = defineProps({
  modelValue: {type: [String, Number], default: ''},
  label: {type: String, default: ''},
});

const hasValue = computed(() => props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '');
</script>

<style scoped lang="scss">
.clienvy-display-text {
  display: flex;
  align-items: center;
  font-family: var(--clienvy-text-font-family);
  font-size: var(--clienvy-form-field-font-size);
  font-weight: var(--clienvy-form-field-font-weight);
  color: var(--clienvy-form-field-color);
  word-break: break-word;

  .clienvy-display-text-empty {
    color: var(--clienvy-form-field-placeholder-color);
  }
}
</style>
