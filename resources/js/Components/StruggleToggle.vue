<template>
    <button
        v-if="user"
        type="button"
        :data-testid="`struggle-toggle-${wordId}`"
        :data-in-struggles="inStruggles ? 'true' : 'false'"
        class="inline-flex items-center justify-center min-h-9 min-w-9 text-lg font-bold rounded-md focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50"
        :class="inStruggles
            ? 'text-red-600 hover:bg-red-50 focus:ring-red-500'
            : 'text-green-600 hover:bg-green-50 focus:ring-green-500'"
        :title="inStruggles ? 'Remove from My Struggles' : 'Add to My Struggles'"
        :disabled="pending"
        @click.prevent.stop="toggle"
    >
        {{ inStruggles ? '−' : '+' }}
    </button>
</template>

<script>
    export default {
        name: 'struggle-toggle',
    }
</script>

<script setup>
    import { computed, ref, watch } from 'vue'
    import { useStore } from 'vuex'
    import { useAxiosRequest } from '../Reusables/AxiosRequest'

    const props = defineProps({
        wordId: { type: [Number, String], required: true },
        inStruggles: { type: Boolean, default: false },
    })

    const emits = defineEmits(['changed'])

    const store = useStore()
    const { axiosRequest } = useAxiosRequest()
    const user = computed(() => store.state.user)
    const inStruggles = ref(props.inStruggles)
    const pending = ref(false)

    watch(() => props.inStruggles, (value) => {
        inStruggles.value = value
    })

    function toggle() {
        if (pending.value) {
            return
        }

        pending.value = true
        const adding = !inStruggles.value
        const endpoint = `/api/struggles/${props.wordId}`
        const method = adding ? 'post' : 'delete'

        axiosRequest(endpoint, {}, method, true)
            .then(() => {
                inStruggles.value = adding
                emits('changed', { wordId: Number(props.wordId), inStruggles: adding })
            })
            .catch((error) => {
                const msg = error?.data?.msg
                alert(msg || 'Error. Something went wrong!')
            })
            .finally(() => {
                pending.value = false
            })
    }
</script>
