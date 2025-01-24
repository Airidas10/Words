import { createStore } from 'vuex'

export default createStore({
    state: {
        searchString: null,
    },

    getters: {

    },

    actions: {

    },

    mutations: {
        setSearchString(state, searchString){
            state.searchString = searchString
        }
    }
})