<template>
    <div class="w-full">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto w-full">
                <header class="text-center mb-6">
                    <h1 class="text-4xl font-bold text-gray-800">{{ tag.tag }}</h1>
                    <InertiaLink href="/tags" class="inline-block mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                        &larr; Back to Tags
                    </InertiaLink>
                </header>

                <div v-if="tag.words?.length > 0" class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Related Words:</h3>
                    <button
                        type="button"
                        class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm"
                        @click="toggleTranslation"
                    >
                        {{ showTranslation ? 'Hide Translation' : 'Show Translation' }}
                    </button>
                </div>

                <div
                    v-if="tag.words?.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-full"
                >
                    <div v-for="word in tag.words" :key="word.id">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card :word="word" :stats="statsForWord(wordStats, word.id)" @tagClick="handleTagClick"></word-card>
                        </InertiaLink>
                    </div>
                </div>

                <div v-else class="mt-6 w-full text-center">
                    <p class="text-gray-600">No related words.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'tag-page',
    }
</script>

<script setup>
    import { computed } from 'vue'
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    import WordCard from '../Components/WordCard.vue'
    import { statsForWord } from '../Reusables/wordStatsDisplay'

    const store = useStore()

    const props = defineProps({
        tag: Object,
        wordStats: { type: Object, default: null },
    })

    const showTranslation = computed(() => store.state.showTranslation)

    function handleTagClick(data) {
        store.commit('setSearchData', {
            type: 'tag',
            tag: data.tag.tag,
        })
    }

    function toggleTranslation() {
        store.commit('setShowTranslation', !showTranslation.value)
    }
</script>
