<template>
    <div class="homepage">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto">
                <header class="text-center mb-10">
                    <h1 class="text-4xl font-bold text-gray-800">CHANGEME TITLE</h1>
                    <p class="text-gray-600">CHANGEME DESCRIPTION</p>
                </header>

                <div class="flex justify-between items-center mb-6">
                    <InertiaLink href="/words/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Create New
                    </InertiaLink>

                    <button @click="toggleTranslation" class="text-gray-600 hover:text-gray-800">
                        {{ showTranslation ? 'Hide' : 'Show' }}
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="word in words">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card :word="word" :show-translation="showTranslation" @tagClick="handleTagClick"></word-card>
                        </InertiaLink>
                    </div>
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

    const { axiosRequest } = useAxiosRequest()
    const store = useStore()

    // Props
    const props = defineProps({
        wordsList : {type: Array},
    })

    const words = ref([])

    watch(() => props.wordsList, (newValue, oldValue) => {
            words.value = props.wordsList
        }, {deep: true, immediate: true}
    )

    function handleTagClick(data){
        let tag = data.tag
        console.log("handleTagClick", tag)

        // Trigger search with tag
    }

    const searchString = computed(() => {
        return store.state.searchString
    })

    watch(searchString, (newValue, oldValue) => {
        let searchType = 'global'
        search(searchType, newValue)
    })

    function search(type, string){
        let method = 'GET'
        let endpoint = '/api/search/' + type + '/' + string
        let apiData = null

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
            if(response.status == 'success'){
                words.value = response.data
            } else{
                alert("Error. Something went wrong!")
            }
        })
    }

    const showTranslation = ref(true)

    function toggleTranslation(){
        showTranslation.value = !showTranslation.value
    }
</script>
