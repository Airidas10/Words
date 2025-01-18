<template>
    <div class="flex flex-col min-h-screen">
        <header class="bg-blue-800 text-white sticky top-0 z-50 shadow">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <InertiaLink href="/">
                    <h1 class="text-lg font-bold">CHANGEME</h1>
                </InertiaLink>

                <div class="lg:block">
                    <input type="text" class="rounded bg-white text-gray-900 px-3 py-1 text-sm placeholder-gray-500 focus:outline-none focus:ring focus:ring-blue-500" placeholder="Search..." v-model="searchString" @input="handleSearch"/>
                </div>

                <button class="text-white block lg:hidden focus:outline-none" @click="isMenuOpen = !isMenuOpen" aria-label="Toggle navigation menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
                <nav :class="['lg:flex', isMenuOpen ? 'block' : 'hidden']" class="absolute lg:static top-12 left-0 bg-blue-800 w-full lg:w-auto lg:bg-transparent lg:space-x-4 space-y-2 lg:space-y-0">
                    <InertiaLink v-for="(link, index) in links" :key="index" :href="link.href" class="block lg:inline-block text-sm text-blue-300 hover:text-white px-4 py-2 lg:p-0">
                        {{ link.label }}
                    </InertiaLink>
                </nav>
            </div>
        </header>

        <main class="flex-grow container mx-auto px-4 py-6">
            <slot />
        </main>

        <!-- <footer class="bg-blue-800 text-white text-sm py-4 text-center">
            © 2025 My App. All rights reserved.
        </footer> -->
    </div>
</template>

<script>
    export default{
        name: 'layout'
    }
</script>

<script setup> 
    // Vue stuff
    import { ref } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { debounce } from 'lodash'

    const props = defineProps({
        // pass shared props if needed
    })

    const isMenuOpen = ref(false)

    const links = ref([
        { href: '/', label: 'Home' },
        { href: '/random', label: 'Random' },
        { href: '/tags', label: 'Tags' },
    ])

    const searchString = ref('')

    const handleSearch = debounce(() => {
        console.log("handleSearch", searchString.value)


    }, 500)
</script>

<style scoped>
    /* Add any global styles for the topbar */
</style>