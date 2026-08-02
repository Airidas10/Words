<template>
    <div class="w-full">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto w-full">
                <header class="text-center mb-6">
                    <h1 class="text-4xl font-bold text-gray-800">My Struggles</h1>
                    <InertiaLink href="/" class="inline-block mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                        &larr; Back to Words
                    </InertiaLink>
                </header>

                <div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
                    <h3 v-if="words.length > 0" class="text-xl font-semibold text-gray-800">Words to focus on:</h3>
                    <span v-else></span>

                    <div class="flex items-center gap-4 flex-wrap ml-auto">
                        <StruggleProposals
                            :listed-ids="listedIds"
                            @wordAdded="handleProposalAdded"
                        />
                        <button
                            v-if="words.length > 0"
                            type="button"
                            class="text-blue-600 hover:text-blue-800 cursor-pointer text-sm"
                            @click="toggleTranslation"
                        >
                            {{ showTranslation ? 'Hide Translation' : 'Show Translation' }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="words.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-full"
                >
                    <div v-for="word in words" :key="word.id">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card
                                :word="word"
                                :stats="statsForWord(localWordStats, word.id)"
                                :in-struggles="Boolean(word.in_struggles)"
                                @tagClick="handleTagClick"
                                @struggleChanged="handleStruggleChanged"
                            ></word-card>
                        </InertiaLink>
                    </div>
                </div>

                <div v-else class="mt-6 w-full text-center">
                    <p class="text-gray-600">No struggle words yet. Add some from word cards.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'my-struggles',
    }
</script>

<script setup>
    import { computed, ref, watch } from 'vue'
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    import WordCard from '../Components/WordCard.vue'
    import StruggleProposals from '../Components/StruggleProposals.vue'
    import { statsForWord } from '../Reusables/WordStatsDisplay'

    const store = useStore()

    const props = defineProps({
        words: { type: Array, default: () => [] },
        wordStats: { type: Object, default: null },
    })

    const words = ref([...props.words])
    const localWordStats = ref({ ...(props.wordStats ?? {}) })

    watch(() => props.words, (value) => {
        words.value = [...value]
    }, { deep: true })

    watch(() => props.wordStats, (value) => {
        localWordStats.value = { ...(value ?? {}) }
    }, { deep: true })

    const listedIds = computed(() => words.value.map((word) => word.id))
    const showTranslation = computed(() => store.state.showTranslation)

    function toggleTranslation() {
        store.commit('setShowTranslation', !showTranslation.value)
    }

    function handleTagClick({ tag }) {
        store.commit('setSearchData', { type: 'tag', tag: tag.tag })
    }

    function handleStruggleChanged({ wordId, inStruggles }) {
        if (!inStruggles) {
            words.value = words.value.filter((word) => word.id !== wordId)
        }
    }

    function handleProposalAdded({ word, stats }) {
        const id = Number(word.id)
        if (words.value.some((item) => Number(item.id) === id)) {
            return
        }

        words.value = [{ ...word, in_struggles: true }, ...words.value]

        if (stats) {
            localWordStats.value = {
                ...localWordStats.value,
                [String(id)]: stats,
            }
        }
    }
</script>
