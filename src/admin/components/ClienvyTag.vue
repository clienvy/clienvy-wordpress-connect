<template>
	<span class="clienvy-tag" :class="`clienvy-tag--${color}`">{{ label }}</span>
</template>

<script setup>
defineProps({
	label: { type: String, required: true },
	color: {
		type: String,
		default: 'gray',
		validator: (v) => ['gray', 'green', 'red'].includes(v),
	},
});
</script>

<style scoped lang="scss">
.clienvy-tag {
	display: inline-flex;
	align-items: center;
	padding: var(--clienvy-tag-padding);
	border-radius: var(--clienvy-tag-border-radius);
	font-size: var(--clienvy-tag-font-size);
	font-weight: 400;
	line-height: 1;
	width: fit-content;
	background: var(--clienvy-tag-background-color);
	color: var(--clienvy-tag-color);
}

.clienvy-tag--gray {
	--clienvy-tag-background-color: var(--clienvy-tag-gray-background-color);
	--clienvy-tag-color: var(--clienvy-tag-gray-color);
}

.clienvy-tag--green {
	--clienvy-tag-background-color: var(--clienvy-tag-green-background-color);
	--clienvy-tag-color: var(--clienvy-tag-green-color);
}

.clienvy-tag--red {
	--clienvy-tag-background-color: var(--clienvy-tag-red-background-color);
	--clienvy-tag-color: var(--clienvy-tag-red-color);
}
</style>
