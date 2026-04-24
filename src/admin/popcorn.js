import {reactive} from 'vue';

const MAX_ITEMS = 5;
const DURATION = 4000;

let nextId = 1;
const timers = new Map();

export const popcornState = reactive({
    items: [],
});

export function popcorn(type, text) {
    const id = nextId++;
    const entry = {id, type, text: String(text ?? '')};
    popcornState.items.push(entry);

    while (popcornState.items.length > MAX_ITEMS) {
        const removed = popcornState.items.shift();
        clearTimer(removed.id);
    }

    const timer = setTimeout(() => close(id), DURATION);
    timers.set(id, timer);

    return {
        close() {
            clearTimer(id);
            close(id);
        },
    };
}

export function dismissPopcorn(id) {
    clearTimer(id);
    close(id);
}

function clearTimer(id) {
    const timer = timers.get(id);
    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }
}

function close(id) {
    const index = popcornState.items.findIndex((item) => item.id === id);
    if (index >= 0) popcornState.items.splice(index, 1);
}
