<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white rounded-lg shadow-md flex flex-col items-center">
        <div class="flex items-center justify-center w-full mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">{{ tag.tag }}</h1>
        </div>

        <div class="mt-6 mb-6 w-full text-center">
            <InertiaLink href="/tags" class="text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Tags
            </InertiaLink>
        </div>

        <h3 v-if="tag.words?.length > 0" class="text-xl font-semibold text-gray-800 mb-6">Related Words:</h3>
        <div v-if="tag.words?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
            <div v-for="word in tag.words">
                <InertiaLink :href="`/words/${word.id}`">
                    <word-card :word="word" @tagClick="handleTagClick"></word-card>
                </InertiaLink>
            </div>
        </div>

        <div v-else class="mt-6 w-full text-center">
            <p class="text-gray-600">No tags available.</p>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'tag-page'
    }
</script>

<script setup>
    // Vue stuff
    import { computed } from 'vue' 
    // // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
    // Components
    import WordCard from '../Components/WordCard.vue'
    // import { useStore } from 'vuex'

    // const store = useStore()

    // Props
    const props = defineProps({
        tag: Object
    })

    function handleTagClick(){
        console.log("handleTagClick TODO")
    }

    // const showTranslation = computed(() => store.state.showTranslation)

    // function toggleTranslationVisibility(){
    //     store.commit('setShowTranslation', !showTranslation.value)
    // }

</script>

