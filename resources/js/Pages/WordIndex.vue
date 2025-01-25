<template>
    <div class="homepage">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto">
                <header class="text-center mb-10">
                    <h1 class="text-4xl font-bold text-gray-800">CHANGEME TITLE</h1>
                    <p class="text-gray-600">CHANGEME DESCRIPTION</p>
                </header>

                <div v-if="isSearching" class="mt-8 text-center">
                    <h3 v-html="searchHeadline"></h3>
                    <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        &larr; Back to Words
                    </InertiaLink>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <button v-if="words?.length > 0" @click="toggleTranslation" class="text-gray-600 hover:text-gray-800">
                        {{ showTranslation ? 'Hide Translation' : 'Show Translation' }}
                    </button>

                    <InertiaLink v-if="!isSearching" href="/words/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Create New
                    </InertiaLink>
                </div>

                <div v-if="words?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="word in words">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card :word="word" :show-translation="showTranslation" @tagClick="handleTagClick"></word-card>
                        </InertiaLink>
                    </div>
                </div>

                <div v-if="words?.length == 0" class="text-center">
                    Nothing to show here
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-index'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, computed, watch } from 'vue'
    // Libraries
    import { Link as InertiaLink, router } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'
    // Components
    import WordCard from '../Components/WordCard.vue'

    const { axiosRequest } = useAxiosRequest()
    const store = useStore()

    // Props
    const props = defineProps({
        wordsList : {type: Array, default: []},
        isSearching: {type: Boolean, default: false},
        searchData: {type: Object, default: {}},
    })

    const words = ref([])

    watch(() => props.wordsList, (newValue, oldValue) => {
            words.value = props.wordsList
        }, {deep: true, immediate: true}
    )

    function handleTagClick(data){
        let tagObj = data.tag
        let searchType = 'tag'
        search(searchType, tagObj.tag)
    }

    const searchString = computed(() => {
        return store.state.searchString
    })

    const searchHeadline = computed(() => {
        let headline = 'Search results:'
        if(props.searchData.hasOwnProperty('type')){
            if(props.searchData.type == 'tag'){
                headline = 'Search results for a <strong>tag</strong> named <strong>' + props.searchData.searchString + '</strong>'
            } else if(props.searchData.type == 'global'){
                headline = 'Search results for words containing <strong>' + props.searchData.searchString + '</strong>'
            }
        }

        return headline
    })

    watch(searchString, (newValue, oldValue) => {
        let searchType = 'global'
        search(searchType, newValue)
    })

    function search(type, string){
        router.visit('/search/' + type + '/' + string)
    }

    const showTranslation = ref(true)

    function toggleTranslation(){
        showTranslation.value = !showTranslation.value
    }
</script>
