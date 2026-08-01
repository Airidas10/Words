<template>
    <div v-if="user && variant === 'compact'" class="relative shrink-0 z-20">
        <button
            type="button"
            :data-testid="`word-stats-${wordId}`"
            class="inline-flex items-center justify-center min-h-9 min-w-9 px-2 text-sm font-medium rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
            :class="colorClass"
            @click.prevent.stop="toggleStatsPopover"
        >
            {{ badgeLabel }}
        </button>

        <div
            v-if="isStatsPopoverOpen"
            :data-testid="`word-stats-popover-${wordId}`"
            class="absolute bottom-full right-0 mb-2 w-56 max-w-[min(16rem,calc(100vw-2rem))] bg-white shadow-lg rounded-lg p-4 border border-gray-200 z-50"
            @click.prevent.stop
        >
            <p class="text-sm" :class="colorClass">
                {{ detailLabel }}
            </p>
            <button
                type="button"
                class="text-red-500 text-xs mt-2 underline"
                @click.prevent.stop="toggleStatsPopover"
            >
                Close
            </button>
        </div>
    </div>

    <p
        v-else-if="user && variant === 'detailed'"
        :data-testid="`word-stats-${wordId}`"
        class="mt-6 w-full text-center text-sm"
        :class="colorClass"
    >
        {{ detailLabel }}
    </p>
</template>

<script>
    export default {
        name: 'word-overall-stats',
    }
</script>

<script setup>
    import { computed, ref } from 'vue'
    import { useStore } from 'vuex'
    import { formatAccuracy, accuracyColorClass } from '../Reusables/WordStatsDisplay'

    const props = defineProps({
        stats: { type: Object, default: null },
        wordId: { type: [Number, String], required: true },
        variant: {
            type: String,
            default: 'compact',
            validator: (value) => ['compact', 'detailed'].includes(value),
        },
    })

    const store = useStore()
    const user = computed(() => store.state.user)
    const isStatsPopoverOpen = ref(false)

    const hasStats = computed(() => Boolean(props.stats?.overall))

    const badgeLabel = computed(() => {
        if (!hasStats.value) {
            return '—'
        }

        return `${formatAccuracy(props.stats.overall.accuracy)}%`
    })

    const detailLabel = computed(() => {
        if (!hasStats.value) {
            return 'Not tested yet'
        }

        const overall = props.stats.overall

        return `Your stats: ${formatAccuracy(overall.accuracy)}% (${overall.correct}/${overall.attempts})`
    })

    const colorClass = computed(() => {
        if (!hasStats.value) {
            return accuracyColorClass(null)
        }

        return accuracyColorClass(props.stats.overall.accuracy)
    })

    function toggleStatsPopover() {
        isStatsPopoverOpen.value = !isStatsPopoverOpen.value
    }
</script>
