<template>
    <div class="error">
        <div class="min-h-screen bg-gray-100 p-6">
            <div class="mt-8 text-center">
                <h1 class="text-4xl font-bold text-gray-800">{{ message }}</h1>
                <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    &larr; Back to Words
                </InertiaLink>
            </div>    
        </div>
    </div>
</template>

<script>
    export default{
        name: 'error'
    }
</script>

<script setup>
    // Vue stuff

    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
        message : {type: String, default: 'Something went wrong!'},
    })
</script>