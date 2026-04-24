<template>
  <button
      class="clienvy-button"
      :class="[
			`clienvy-button--${variant}`,
			{
				'is-spinning': spinning,
				'is-full-size': fullSize,
			},
		]"
      :disabled="disabled || spinning"
      type="button"
      @click="$emit('click', $event)"
  >
    <span v-if="label || $slots.default" class="clienvy-button-label">
			<template v-if="label">{{ label }}</template>
			<slot v-else/>
		</span>

    <span v-if="spinning" class="clienvy-button-spinner" aria-hidden="true"/>
  </button>
</template>

<script setup>
defineProps({
  label: {type: String, default: ''},
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'danger', 'flat'].includes(v),
  },
  disabled: {type: Boolean, default: false},
  spinning: {type: Boolean, default: false},
  fullSize: {type: Boolean, default: false},
});

defineEmits(['click']);
</script>

<style scoped lang="scss">
.clienvy-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 15px;

  background: var(--clienvy-button-background-color);
  color: var(--clienvy-button-color);
  border-width: var(--clienvy-button-border-width);
  border-style: solid;
  border-color: var(--clienvy-button-border-color);
  border-radius: var(--clienvy-button-border-radius);
  box-shadow: var(--clienvy-button-box-shadow);
  padding: var(--clienvy-button-padding);
  height: var(--clienvy-button-height);
  font-family: var(--clienvy-button-font-family);
  font-size: var(--clienvy-button-font-size);
  font-weight: var(--clienvy-button-font-weight);
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: transform 0.2s ease-in-out, background 0.2s, color 0.2s;

  &:hover:not(:disabled) {
    background: var(--clienvy-button-hover-background-color);
    color: var(--clienvy-button-hover-color);
    transform: scale(1.01);
  }

  &:disabled {
    opacity: 0.8;
    cursor: not-allowed;
  }

  &.is-spinning {
    cursor: progress;

    &:hover {
      transform: none;
    }
  }

  &.is-full-size {
    width: 100%;
  }

  .clienvy-button-spinner {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid currentColor;
    border-top-color: transparent;
    animation: clienvy-button-spin 0.8s linear infinite;
  }
}

.clienvy-button--flat {
  --clienvy-button-background-color: var(--clienvy-button-flat-background-color);
  --clienvy-button-color: var(--clienvy-button-flat-color);
  --clienvy-button-hover-background-color: var(--clienvy-button-flat-hover-background-color);
  --clienvy-button-hover-color: var(--clienvy-button-flat-hover-color);
}

.clienvy-button--danger {
  --clienvy-button-background-color: var(--clienvy-button-danger-background-color);
  --clienvy-button-color: var(--clienvy-button-danger-color);
  --clienvy-button-hover-background-color: var(--clienvy-button-danger-hover-background-color);
  --clienvy-button-hover-color: var(--clienvy-button-danger-hover-color);
}

@keyframes clienvy-button-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
