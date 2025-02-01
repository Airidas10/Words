<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white rounded-lg shadow-md flex flex-col items-center">
        <div class="flex items-center justify-center w-full mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">{{ word.word }}</h1>
            <InertiaLink v-if="isRandomPage" href="/random" class="text-blue-500 text-sm ml-2 hover:underline">Regenerate</InertiaLink>

        </div>
        <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm mb-10" @click="toggleTranslationVisibility">
            {{ showTranslation ? 'Hide' : 'Show' }} Translation
        </span>

        <p v-for="translation in word.translations" :key="translation.id" class="text-gray-600 flex items-center justify-center">
            <span>{{ showTranslation ? translation.translation : '*****' }}</span>
        </p>

        <div v-if="word.tags.length" class="mt-6 w-full">
            <h3 class="text-lg font-medium text-gray-800 text-center">Tags:</h3>
            <ul class="flex flex-wrap justify-center gap-2 mt-2">
                <li v-for="tag in word.tags" :key="tag.id" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm">
                    {{ tag.tag }}
                </li>
            </ul>
        </div>

        <div v-else class="mt-6 w-full text-center">
            <p class="text-gray-600">No tags available.</p>
        </div>

        <div class="mt-6 w-full text-center">
            <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Words
            </InertiaLink>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-page'
    }
</script>

<script setup>
    // Vue stuff
    import { computed } from 'vue' 
    // Libraries
    import { Link as InertiaLink, usePage } from '@inertiajs/vue3'
    import { useStore } from 'vuex'

    const store = useStore()
    const { url } = usePage()

    // Props
    const props = defineProps({
        word: Object
    })

    const isRandomPage = computed(() => {
        return (url === '/random') ? true : false
    })

    const showTranslation = computed(() => store.state.showTranslation)

    function toggleTranslationVisibility(){
        store.commit('setShowTranslation', !showTranslation.value)
    }

</script>

<style scoped>
    .word-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
</style>
