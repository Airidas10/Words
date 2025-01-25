import { createStore } from 'vuex'

export default createStore({
    state: {
        searchString: null,
        showTranslation: true,
    },

    getters: {

    },

    actions: {

    },

    mutations: {
        setSearchString(state, searchString){
            state.searchString = searchString
        },

        setShowTranslation(state, showTranslation){
            state.showTranslation = showTranslation
        },
    }
})