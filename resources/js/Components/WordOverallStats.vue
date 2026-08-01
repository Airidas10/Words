<template>
    <p
        v-if="stats?.overall"
        :data-testid="`word-stats-${wordId}`"
        :class="variant === 'detailed' ? 'mt-6 w-full text-center text-gray-600 text-sm' : 'text-sm text-gray-500 mt-2'"
    >
        <template v-if="variant === 'detailed'">
            Your stats: {{ formatAccuracy(stats.overall.accuracy) }}%
            ({{ stats.overall.correct }}/{{ stats.overall.attempts }})
        </template>
        <template v-else>
            {{ formatAccuracy(stats.overall.accuracy) }}%
        </template>
    </p>
</template>

<script>
    export default {
        name: 'word-overall-stats',
    }
</script>

<script setup>
    import { formatAccuracy } from '../Reusables/formatAccuracy'

    defineProps({
        stats: { type: Object, default: null },
        wordId: { type: [Number, String], required: true },
        variant: {
            type: String,
            default: 'compact',
            validator: (value) => ['compact', 'detailed'].includes(value),
        },
    })
</script>
