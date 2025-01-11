<template>
    <div class="homepage">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <header class="text-center mb-10">
                    <h1 class="text-4xl font-bold text-gray-800">CHANGEME TITLE</h1>
                    <p class="text-gray-600">CHANGEME DESCRIPTION</p>
                </header>

                <form class="mb-6">
                    <input v-model="searchString" type="text" name="search" placeholder="Search..." class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200" @input="handleSearch">
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="word in words">
                        <InertiaLink :href="`/words/${word.id}`">
                            <word-card :word="word" @tagClick="handleTagClick"></word-card>
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
    import { ref } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { debounce } from 'lodash'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'
    // Components
    import WordCard from '../Components/WordCard.vue'

    const { axiosRequest } = useAxiosRequest()

    // Props
    const props = defineProps({
        words : {type: Array},
    })

    const searchString = ref('')

    const handleSearch = debounce(() => {
        console.log("handleSearch", searchString.value)


    }, 500)

    function handleTagClick(data){
        let tag = data.tag
        console.log("handleTagClick", tag)

        // Trigger search with tag
    }

    function search(data){
        let endpoint = 'TODO'

        let method = 'POST'

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
        })
    }
</script>
