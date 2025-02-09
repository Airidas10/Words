<template>
    <div class="p-6">
        <div class="flex items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800 flex-1 text-center">Tags</h1>
            <div>
                <InertiaLink href="/tags/create" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create New
                </InertiaLink>
            </div>
        </div>

        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Tag</th>
                    <th class="px-4 py-2 text-left">Word Count</th>
                    <th class="px-4 py-2 text-left invisible">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(tag, index) in tags" :key="index" class="hover:bg-gray-100 cursor-pointer" @click.prevent.stop="handleRowClick(tag)">
                    <td class="px-4 py-2">{{ tag.tag }}</td>
                    <td class="px-4 py-2">{{ tag.words_count }}</td>
                    <td class="px-4 py-2 relative">
                        <InertiaLink @click.prevent.stop :href="`/tags/edit/${tag.id}`" class="absolute inset-y-0 left-0 right-0 flex items-center justify-center text-sm text-blue-600 hover:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L6 11.172V14h2.828l8.586-8.586a2 2 0 000-2.828zM7 12v-1.414l8.586-8.586a1 1 0 011.414 1.414L8.414 12H7z"/>
                                <path fill-rule="evenodd" d="M4 15a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
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
    import { Link as InertiaLink, router } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
        tags : {type: Array},
    })

    function handleRowClick(tag){
        router.visit(/tags/ + tag.id)
    }
</script>