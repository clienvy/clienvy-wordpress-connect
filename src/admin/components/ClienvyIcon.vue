<template>
	<span
		v-if="svg"
		class="clienvy-icon"
		:style="{ width: size + 'px', height: size + 'px' }"
		v-html="svg"
	/>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
	name: {
		type: String,
		required: true,
	},
	size: {
		type: Number,
		default: 16,
	},
});

const icons = import.meta.glob('../icons/*.svg', { eager: true, query: '?raw', import: 'default' });

const svg = computed(() => icons[`../icons/${props.name}.svg`] || '');
</script>

<style scoped lang="scss">
.clienvy-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	line-height: 0;

	:deep(svg) {
		width: 100%;
		height: 100%;
		fill: currentColor;
	}
}
</style>
