<template>
    <div class="p-6">
        <div class="flex justify-center mb-4">    
            <h1 class="text-2xl font-semibold text-gray-800">My Tests</h1>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Create Date</th>
                        <th class="px-4 py-2 text-left">Finish Date</th>
                        <th class="px-4 py-2 text-left">Score</th>
                        <th class="px-4 py-2 text-left invisible">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(test, index) in tests" :key="index" class="hover:bg-gray-100 cursor-pointer" @click.prevent.stop="handleRowClick(test)">
                        <td class="px-4 py-2">{{ test.created_at }}</td>
                        <td class="px-4 py-2">{{ test.updated_at }}</td>
                        <td class="px-4 py-2">{{ getScore(test) }}</td>
                        <td class="px-4 py-2 relative">
                            <InertiaLink @click.prevent.stop :href="`/runs/${test.id}`" class="absolute inset-y-0 left-0 right-0 flex items-center justify-center text-sm text-blue-600 hover:underline">
                                See more
                            </InertiaLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :pagination-data="userTests.links" />
    </div>
</template>


<script>
    export default{
        name: 'my-tests'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, watch } from 'vue'
    // Libraries
    import { Link as InertiaLink, router } from '@inertiajs/vue3'

    // Components
    import WordCard from '../Components/WordCard.vue'
    import Pagination from '../Components/Pagination.vue'

    // Props
    const props = defineProps({
        userTests : {type: Object, default: {}},
    })

    const tests = ref([])
    watch(() => props.userTests, (newValue, oldValue) => {
            tests.value = props.userTests.data
        }, {deep: true, immediate: true}
    )

    function getScore(test){
        return test.score + '/' + test.number_of_questions
    }

    function handleRowClick(test){
        router.visit('/runs/' + test.id)
    }
</script>