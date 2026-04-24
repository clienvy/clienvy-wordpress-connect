<template>
  <form class="clienvy-form" @submit.prevent="submit" novalidate>
    <slot/>
  </form>
</template>

<script setup>
defineProps({
  submit: {
    type: Function, default: () => {
    }
  },
});
</script>

<style scoped lang="scss">
.clienvy-form {
  display: flex;
  flex-direction: column;
  gap: var(--clienvy-form-layout-gap);
}
</style>
