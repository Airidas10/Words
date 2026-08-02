<template>
    <div class="inline-block">
        <button
            type="button"
            data-testid="struggle-proposals-trigger"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium cursor-pointer disabled:opacity-50"
            :disabled="pending"
            @click="openSuggestions"
        >
            Suggest from stats
        </button>

        <div
            v-if="open"
            data-testid="struggle-proposals"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeSuggestions"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Suggested from your stats</h2>
                    <button
                        type="button"
                        class="bg-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring focus:ring-gray-400"
                        @click="closeSuggestions"
                    >
                        Close
                    </button>
                </div>

                <div
                    v-if="proposedWords.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"
                >
                    <div v-for="word in proposedWords" :key="word.id">
                        <InertiaLink :href="`/words/${word.id}`">
                            <WordCard
                                :word="word"
                                :stats="statsForWord(wordStats, word.id)"
                                :in-struggles="Boolean(word.in_struggles)"
                                :add-only="true"
                                :show-edit="false"
                                @struggleChanged="onProposalToggled"
                            />
                        </InertiaLink>
                    </div>
                </div>

                <p v-else class="text-gray-600 text-center py-6">No suggestions</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'struggle-proposals',
    }
</script>

<script setup>
    import { ref, watch } from 'vue'
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import WordCard from './WordCard.vue'
    import { useAxiosRequest } from '../Reusables/AxiosRequest'
    import { statsForWord } from '../Reusables/WordStatsDisplay'

    const props = defineProps({
        listedIds: { type: Array, default: () => [] },
    })

    const emits = defineEmits(['wordAdded'])

    const { axiosRequest } = useAxiosRequest()
    const pending = ref(false)
    const open = ref(false)
    const loaded = ref(false)
    const proposedWords = ref([])
    const wordStats = ref({})

    watch(() => props.listedIds, syncListedFlags)

    function syncListedFlags(ids) {
        const listed = new Set((ids ?? []).map(Number))

        proposedWords.value = proposedWords.value.map((word) => ({
            ...word,
            in_struggles: listed.has(Number(word.id)),
        }))
    }

    function openSuggestions() {
        if (pending.value) {
            return
        }

        if (loaded.value) {
            syncListedFlags(props.listedIds)
            open.value = true
            return
        }

        pending.value = true
        axiosRequest('/api/struggles/proposals', {}, 'get', true)
            .then((response) => {
                proposedWords.value = response.data.words ?? []
                wordStats.value = response.data.wordStats ?? {}
                loaded.value = true
                syncListedFlags(props.listedIds)
                open.value = true
            })
            .catch(() => {
                alert('Error. Something went wrong!')
            })
            .finally(() => {
                pending.value = false
            })
    }

    function closeSuggestions() {
        open.value = false
    }

    function onProposalToggled({ wordId, inStruggles }) {
        const match = proposedWords.value.find((word) => Number(word.id) === Number(wordId))
        if (!match) {
            return
        }

        proposedWords.value = proposedWords.value.map((word) => (
            Number(word.id) === Number(wordId)
                ? { ...word, in_struggles: inStruggles }
                : word
        ))

        if (inStruggles) {
            emits('wordAdded', {
                word: { ...match, in_struggles: true },
                stats: statsForWord(wordStats.value, wordId),
            })
        }
    }
</script>
