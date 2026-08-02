<template>
    <div ref="root" class="relative w-full max-w-md mx-auto">
        <label id="random-pool-label" class="block text-sm font-medium text-gray-700 mb-2 text-center">
            Word pool
        </label>

        <button
            id="random-pool-select"
            type="button"
            data-testid="random-pool-select"
            class="flex w-full items-center justify-between rounded-md border border-blue-200 bg-white px-3 py-2 text-left text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-labelledby="random-pool-label"
            @click="toggleOpen"
        >
            <span class="truncate">{{ currentLabel }}</span>
            <span class="ml-2 shrink-0 text-gray-500" aria-hidden="true">{{ open ? '▴' : '▾' }}</span>
        </button>

        <div
            v-if="open"
            data-testid="random-pool-panel"
            class="absolute left-0 right-0 z-30 mt-1 overflow-hidden rounded-md border border-blue-200 bg-white shadow-lg"
            role="listbox"
            aria-labelledby="random-pool-label"
        >
            <div class="border-b border-gray-100 p-2">
                <input
                    ref="filterInput"
                    v-model="filter"
                    type="search"
                    data-testid="random-pool-filter"
                    class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Filter tags..."
                    autocomplete="off"
                    @keydown.esc.prevent="close"
                >
            </div>

            <div class="max-h-[45vh] overflow-y-auto overscroll-contain">
                <div class="border-b border-gray-200">
                    <button
                        type="button"
                        role="option"
                        data-testid="random-pool-option-all"
                        class="block w-full px-3 py-2.5 text-left text-sm hover:bg-blue-50"
                        :class="{ 'bg-blue-50 font-medium text-blue-800': value === 'all' }"
                        :aria-selected="value === 'all'"
                        @click="choose('all')"
                    >
                        Full Random
                    </button>

                    <button
                        v-if="user"
                        type="button"
                        role="option"
                        data-testid="random-pool-option-struggles"
                        class="block w-full px-3 py-2.5 text-left text-sm hover:bg-blue-50"
                        :class="{ 'bg-blue-50 font-medium text-blue-800': value === 'struggles' }"
                        :aria-selected="value === 'struggles'"
                        @click="choose('struggles')"
                    >
                        My Struggles
                    </button>
                </div>

                <button
                    v-for="tag in filteredTags"
                    :key="tag.id"
                    type="button"
                    role="option"
                    :data-testid="`random-pool-option-tag-${tag.id}`"
                    class="block w-full px-3 py-2.5 text-left text-sm hover:bg-blue-50"
                    :class="{ 'bg-blue-50 font-medium text-blue-800': value === `tag:${tag.id}` }"
                    :aria-selected="value === `tag:${tag.id}`"
                    @click="choose(`tag:${tag.id}`)"
                >
                    {{ tag.tag }}
                </button>

                <p
                    v-if="filteredTags.length === 0 && filter.trim() !== ''"
                    class="px-3 py-2.5 text-sm text-gray-500"
                >
                    No matching tags
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'random-pool-picker',
    }
</script>

<script setup>
    import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

    const props = defineProps({
        value: { type: String, required: true },
        tags: { type: Array, default: () => [] },
        user: { type: Object, default: null },
    })

    const emits = defineEmits(['change'])

    const open = ref(false)
    const filter = ref('')
    const root = ref(null)
    const filterInput = ref(null)

    const currentLabel = computed(() => {
        if (props.value === 'struggles') {
            return 'My Struggles'
        }

        if (props.value.startsWith('tag:')) {
            const tagId = props.value.slice('tag:'.length)
            const tag = props.tags.find((item) => String(item.id) === String(tagId))
            return tag?.tag ?? 'Tag'
        }

        return 'Full Random'
    })

    const filteredTags = computed(() => {
        const query = filter.value.trim().toLowerCase()
        if (query === '') {
            return props.tags
        }

        return props.tags.filter((tag) => String(tag.tag).toLowerCase().includes(query))
    })

    function toggleOpen() {
        if (open.value) {
            close()
            return
        }

        open.value = true
        filter.value = ''
        nextTick(() => {
            filterInput.value?.focus()
        })
    }

    function close() {
        open.value = false
        filter.value = ''
    }

    function choose(value) {
        close()
        if (value !== props.value) {
            emits('change', value)
        }
    }

    function onDocumentPointerDown(event) {
        if (!open.value || !root.value) {
            return
        }

        if (!root.value.contains(event.target)) {
            close()
        }
    }

    function onDocumentKeydown(event) {
        if (event.key === 'Escape' && open.value) {
            close()
        }
    }

    onMounted(() => {
        document.addEventListener('pointerdown', onDocumentPointerDown)
        document.addEventListener('keydown', onDocumentKeydown)
    })

    onBeforeUnmount(() => {
        document.removeEventListener('pointerdown', onDocumentPointerDown)
        document.removeEventListener('keydown', onDocumentKeydown)
    })

    watch(() => props.value, () => {
        close()
    })
</script>
