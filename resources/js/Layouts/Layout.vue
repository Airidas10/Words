<template>
    <div class="flex flex-col min-h-screen">
        <header class="bg-blue-800 text-white sticky top-0 z-50 shadow">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <InertiaLink href="/">
                    <h1 class="text-lg font-bold">CHANGEME</h1>
                </InertiaLink>

                <div class="relative lg:block">
                    <input type="text" class="rounded bg-white text-gray-900 px-3 py-1 text-sm placeholder-gray-500 focus:outline-none focus:ring focus:ring-blue-500 w-full pr-12" placeholder="Search..." v-model="searchString"/>
                    
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600 bg-transparent border-none cursor-pointer text-sm" @click.prevent="triggerSearch">
                        Go
                    </button>
                </div>

                <button class="text-white block lg:hidden focus:outline-none" @click="isMenuOpen = !isMenuOpen" aria-label="Toggle navigation menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
                <nav :class="['lg:flex', isMenuOpen ? 'block' : 'hidden']" class="absolute lg:static top-12 left-0 bg-blue-800 w-full lg:w-auto lg:bg-transparent lg:space-x-4 space-y-2 lg:space-y-0">
                    <InertiaLink v-for="(link, index) in links" :key="index" :href="link.href" class="block lg:inline-block text-sm text-blue-300 hover:text-white px-4 py-2 lg:p-0" @click="linkClicked">
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
    import { useStore } from 'vuex'

    const store = useStore()

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

    function triggerSearch(){
        store.commit('setSearchString', searchString.value)
    }

    function linkClicked(){
        isMenuOpen.value = false
    }
</script>

<style scoped>
    /* Add any global styles for the topbar */
</style>