<template>
    <nav class="relative flex justify-center mt-8">
        <template v-for="link in paginationData" :key="link.label">
            <Link v-show="shouldShowLink(link)" :href="link.url ?? ''" v-html="link.label" class="flex items-center justify-center px-3 py-2 text-sm rounded-lg text-gray-600" :class="{ 'bg-gray-400': link.active, '!text-gray-500': !link.url }"/>
        </template>
    </nav>
</template>

<script setup>
     // Vue stuff
    import { computed } from 'vue'
    // Libraries
    import { Link } from '@inertiajs/vue3'

    const props = defineProps({
        paginationData: Array
    })

    const activeLink = computed(() => {
        return props.paginationData.find(page => page.active)
    })

    function shouldShowLink(link){
        let shouldShow = true
        if(link.label.includes('Previous') && activeLink.value?.label == 1){
            shouldShow = false
        }

        let lastPage = props.paginationData.length - 2 // Subtract previous and next pages
        if(link.label.includes('Next') && activeLink.value?.label == lastPage){
            shouldShow = false
        }

        return shouldShow
    }

</script>