<template>
    <div class="homepage">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto">
                <header class="text-center mb-10">
                    <h1 class="text-4xl font-bold text-gray-800">Parole</h1>
                    <p class="text-gray-600">Ho Bisogno Di Imparare L'Italiano! 🤌</p>
                </header>

                <div v-if="isSearching" class="mt-8 text-center">
                    <h3 v-html="searchHeadline"></h3>
                    <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        &larr; Back to Words
                    </InertiaLink>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <button v-if="words?.length > 0" @click="toggleTranslation" class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm">
                        {{ showTranslation ? 'Hide Translation' : 'Show Translation' }}
                    </button>

                    <InertiaLink v-if="(!isSearching && user)" href="/words/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Create New
                    </InertiaLink>
                </div>

                <div v-if="words?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="word in words">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card :word="word" @tagClick="handleTagClick"></word-card>
                        </InertiaLink>
                    </div>
                </div>

                <Pagination :pagination-data="wordsList.links" />

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
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'
    // Components
    import WordCard from '../Components/WordCard.vue'
    import Pagination from '../Components/Pagination.vue'

    const { axiosRequest } = useAxiosRequest()
    const store = useStore()

    // Props
    const props = defineProps({
        wordsList : {type: Object, default: {}},
        isSearching: {type: Boolean, default: false},
        searchData: {type: Object, default: {}},
    })

    const words = ref([])

    watch(() => props.wordsList, (newValue, oldValue) => {
            words.value = props.wordsList.data
        }, {deep: true, immediate: true}
    )

    const user = computed(() => store.state.user)

    function handleTagClick(data){
        let tagObj = data.tag
        let searchType = 'tag'

        let searchData = {type: searchType, tag: tagObj.tag}
        store.commit('setSearchData', searchData)
    }

    const searchHeadline = computed(() => {
        let headline = 'Search results:'
        if(props.searchData.hasOwnProperty('type')){
            if(props.searchData.type == 'tag'){
                headline = 'Search results for a <strong>tag</strong> named <strong>' + props.searchData.searchString + '</strong>'
            } else if(props.searchData.type == 'global'){
                if(props.searchData.searchString){
                    headline = 'Search results for words containing <strong>' + props.searchData.searchString + '</strong>'
                } else{
                    headline = 'Search results for <strong>everything</strong>'
                }
            }
        }

        return headline
    })

    const showTranslation = computed(() => store.state.showTranslation)

    function toggleTranslation(){
        store.commit('setShowTranslation', !showTranslation.value)
    }
</script>
