<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Tags</h1>
            <div>
                <InertiaLink href="/tags/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add
                </InertiaLink>
            </div>
        </div>

        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Tag</th>
                    <th class="px-4 py-2 text-left">Word Count</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(tag, index) in tags" :key="index">
                    <td class="px-4 py-2">{{ tag.id }}</td>
                    <td class="px-4 py-2">{{ tag.tag }}</td>
                    <td class="px-4 py-2">{{ tag.words_count }}</td>
                    <td class="px-4 py-2">
                        <InertiaLink :href="`/tags/edit/${tag.id}`" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 mr-2">
                            Edit
                        </InertiaLink>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>


<script>
    export default{
        name: 'tags-index'
    }
</script>

<script setup>
    // Vue stuff
    import { ref } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
        tags : {type: Array},
    })

</script>