<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-8 text-center">Login</h1>
        <form>
            <div class="mb-6">
                <label for="username" class="block text-base font-medium text-gray-700 mb-2">Username</label>
                <input
                    id="username"
                    type="username"
                    v-model="form.username"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter your username"
                />
            </div>

            <div class="mb-6">
                <label for="password" class="block text-base font-medium text-gray-700 mb-2">Password</label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter your password"
                />
            </div>
            <div v-if="displayError" class="text-red-600 text-center mt-2 mb-6">
                {{ displayError }}
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="submit"
                    class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click.prevent.stop="handleSubmit"
                >
                    Login
                </button>
            </div>
        </form>
    </div>
</template>


<script>
    export default{
        name: 'login'
    }
</script>


<script setup>
    import { ref, computed } from 'vue'
    import { useForm } from '@inertiajs/vue3'

    const props = defineProps({ 
        errors: Object 
    })

    const form = useForm({
        username: '',
        password: '',
    })

    const displayError = computed(() => {
        return Object.values(props.errors) ? Object.values(props.errors)[0] : null
    })

    const handleSubmit = () => {
        form.post('/login')
    }

</script>
