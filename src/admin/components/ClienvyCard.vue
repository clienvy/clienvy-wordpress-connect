<template>
  <div
      class="clienvy-card"
      :class="{
			'is-collapsible': collapsible,
			'is-open': !collapsible || open,
		}"
  >
    <component
        v-if="hasHeader"
        :is="collapsible ? 'button' : 'header'"
        :type="collapsible ? 'button' : undefined"
        class="clienvy-card-header"
        :aria-expanded="collapsible ? open : undefined"
        @click="collapsible && toggle()"
    >
      <div class="clienvy-card-header-main">
        <div v-if="title || tag" class="clienvy-card-title">
          <span v-if="title" class="clienvy-card-title-text">{{ title }}</span>
          <clienvy-tag v-if="tag" :label="tag.label" :color="tag.color"/>
        </div>
        <p v-if="info" class="clienvy-card-info">{{ info }}</p>
      </div>

      <span v-if="collapsible" class="clienvy-card-chevron" aria-hidden="true">
				<clienvy-icon name="chevron" :size="20"/>
			</span>
    </component>

    <div
        v-if="hasBody"
        ref="bodyWrap"
        class="clienvy-card-body-wrap"
        :style="bodyStyle"
        @transitionend="onTransitionEnd"
    >
      <div class="clienvy-card-body">
        <slot/>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, useSlots, ref, watch, nextTick, Comment} from 'vue';
import ClienvyTag from './ClienvyTag.vue';
import ClienvyIcon from './ClienvyIcon.vue';

const props = defineProps({
  title: {type: String, default: ''},
  tag: {type: Object, default: null},
  info: {type: String, default: ''},
  collapsible: {type: Boolean, default: false},
  defaultOpen: {type: Boolean, default: true},
});

const slots = useSlots();

function slotHasContent(slot) {
  const nodes = slot?.() || [];
  return nodes.some((node) => {
    if (node.type === Comment) return false;
    if (typeof node.children === 'string' && node.children.trim() === '') return false;
    return true;
  });
}

const hasHeader = computed(() => !!(props.title || props.tag || props.info));
const hasBody = computed(() => slotHasContent(slots.default));

const open = ref(props.defaultOpen);
const bodyWrap = ref(null);
const bodyStyle = ref(props.defaultOpen ? {} : {height: '0px', overflow: 'hidden'});

watch(
    () => props.defaultOpen,
    (v) => {
      open.value = v;
      bodyStyle.value = v ? {} : {height: '0px', overflow: 'hidden'};
    }
);

function toggle() {
  open.value ? collapse() : expand();
}

async function expand() {
  const el = bodyWrap.value;
  if (!el) {
    open.value = true;
    return;
  }

  bodyStyle.value = {height: '0px', overflow: 'hidden'};
  open.value = true;
  await nextTick();
  const h = el.scrollHeight;
  void el.offsetHeight;
  bodyStyle.value = {height: h + 'px', overflow: 'hidden'};
}

async function collapse() {
  const el = bodyWrap.value;
  if (!el) {
    open.value = false;
    return;
  }
  const h = el.scrollHeight;
  bodyStyle.value = {height: h + 'px', overflow: 'hidden'};
  await nextTick();
  void el.offsetHeight;
  bodyStyle.value = {height: '0px', overflow: 'hidden'};
  open.value = false;
}

function onTransitionEnd(e) {
  if (e.propertyName !== 'height') return;
  if (open.value) bodyStyle.value = {};
}
</script>

<style scoped lang="scss">
.clienvy-card {
  background: var(--clienvy-card-background-color);
  border: var(--clienvy-card-border);
  border-radius: var(--clienvy-card-border-radius);
  box-shadow: var(--clienvy-card-box-shadow);
  margin-bottom: 16px;
  overflow: hidden;
  transition: background 0.2s, box-shadow 0.2s;

  .clienvy-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px 30px;
    width: 100%;
    background: transparent;
    border: 0;
    text-align: left;
    font-family: var(--clienvy-text-font-family);
    color: inherit;
    cursor: default;

    @media only screen and (max-width: 600px) {
      padding: 20px 22px;
    }
  }

  .clienvy-card-header-main {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
    min-width: 0;

    .clienvy-card-title {
      display: flex;
      align-items: center;
      gap: 13px;
      font-size: 18px;
      font-weight: 500;
      color: var(--clienvy-card-header-heading-color);

      .clienvy-card-title-text {
        line-height: 1.2;
      }
    }

    .clienvy-card-info {
      font-size: 15px;
      line-height: 1.45;
      color: var(--clienvy-card-header-text-color);
      margin: 0;
    }
  }

  &.is-collapsible .clienvy-card-header {
    cursor: pointer;
  }

  .clienvy-card-chevron {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    color: var(--clienvy-card-header-icon-color);
    transition: transform 0.25s ease;
  }

  &.is-open .clienvy-card-chevron {
    transform: rotate(180deg);
  }

  .clienvy-card-body-wrap {
    transition: height 0.25s ease;

    .clienvy-card-body {
      padding: 24px 30px;
      display: flex;
      flex-direction: column;

      @media only screen and (max-width: 600px) {
        padding: 20px 22px;
      }
    }
  }

  &.is-collapsible:not(.is-open) .clienvy-card-body-wrap {
    height: 0;
    overflow: hidden;
  }

  .clienvy-card-header + .clienvy-card-body-wrap .clienvy-card-body {
    border-top: 1px solid var(--clienvy-card-divider-color);
  }

}

</style>
