<template>
    <button
        v-if="user"
        type="button"
        :data-testid="testId"
        :data-in-struggles="inStruggles ? 'true' : 'false'"
        class="inline-flex items-center justify-center min-h-9 min-w-9 text-lg font-bold rounded-md focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50"
        :class="buttonClass"
        :title="buttonTitle"
        :disabled="pending || cannotAdd"
        @click.prevent.stop="toggle"
    >
        {{ buttonLabel }}
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
        addOnly: { type: Boolean, default: false },
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

    const cannotAdd = computed(() => props.addOnly && inStruggles.value)
    const isRemoveAction = computed(() => !props.addOnly && inStruggles.value)
    const testId = computed(() => props.addOnly
        ? `struggle-propose-toggle-${props.wordId}`
        : `struggle-toggle-${props.wordId}`)
    const buttonTitle = computed(() => {
        if (cannotAdd.value) {
            return 'Already in My Struggles'
        }

        return isRemoveAction.value ? 'Remove from My Struggles' : 'Add to My Struggles'
    })
    const buttonLabel = computed(() => {
        if (cannotAdd.value) {
            return '✓'
        }

        return isRemoveAction.value ? '−' : '+'
    })
    const buttonClass = computed(() => {
        if (cannotAdd.value) {
            return 'text-blue-600'
        }

        if (isRemoveAction.value) {
            return 'text-red-600 hover:bg-red-50 focus:ring-red-500'
        }

        return 'text-green-600 hover:bg-green-50 focus:ring-green-500'
    })

    function toggle() {
        if (pending.value || cannotAdd.value) {
            return
        }

        pending.value = true
        const adding = !inStruggles.value

        axiosRequest(`/api/struggles/${props.wordId}`, {}, adding ? 'post' : 'delete', true)
            .then(() => {
                inStruggles.value = adding
                emits('changed', { wordId: Number(props.wordId), inStruggles: adding })
            })
            .catch((error) => {
                alert(error?.data?.msg || 'Error. Something went wrong!')
            })
            .finally(() => {
                pending.value = false
            })
    }
</script>
