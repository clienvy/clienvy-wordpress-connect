<template>
  <clienvy-field-label v-if="label" :label="label">
    <div class="clienvy-input-inner" :class="{ 'is-disabled': disabled }">
      <input
          class="clienvy-input-control"
          :type="type"
          :value="modelValue"
          :placeholder="placeholder"
          :disabled="disabled"
          @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>
  </clienvy-field-label>
</template>

<script setup>
import ClienvyFieldLabel from './ClienvyFieldLabel.vue';

defineProps({
  modelValue: {type: [String, Number], default: ''},
  type: {type: String, default: 'text'},
  label: {type: String, default: ''},
  placeholder: {type: String, default: ''},
  disabled: {type: Boolean, default: false},
});

defineEmits(['update:modelValue']);
</script>

<style scoped lang="scss">
.clienvy-input-inner {
  display: flex;
  align-items: stretch;
  background: var(--clienvy-form-field-background-color);
  border: var(--clienvy-form-field-border-width) solid var(--clienvy-form-field-border-color);
  border-radius: var(--clienvy-form-field-border-radius);
  box-shadow: var(--clienvy-form-field-box-shadow);
  min-height: var(--clienvy-form-field-min-height);
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  overflow: hidden;

  &:focus-within {
    border-color: var(--clienvy-form-field-focus-border-color);
    box-shadow: var(--clienvy-form-field-hover-box-shadow);
  }

  &.is-disabled {
    background: var(--clienvy-form-field-disabled-background-color);
  }

  .clienvy-input-control {
    flex: 1;
    width: 100%;
    min-width: 0;
    background: transparent;
    border: 0;
    outline: none;
    padding: var(--clienvy-form-field-input-padding);
    font-family: var(--clienvy-text-font-family);
    font-size: var(--clienvy-form-field-font-size);
    font-weight: var(--clienvy-form-field-font-weight);
    color: var(--clienvy-form-field-color);

    &:focus {
      border: 0;
      outline: none;
      box-shadow: none;
    }

    &::placeholder {
      color: var(--clienvy-form-field-placeholder-color);
      font-weight: 400;
    }

    &:disabled {
      color: var(--clienvy-form-field-disabled-color);
      cursor: not-allowed;
    }
  }
}
</style>
