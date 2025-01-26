import { createStore } from 'vuex'

export default createStore({
    state: {
        searchData: null,
        showTranslation: true,
        user: null,
    },

    getters: {

    },

    actions: {

    },

    mutations: {
        setSearchData(state, searchData){
            state.searchData = searchData
        },

        setShowTranslation(state, showTranslation){
            state.showTranslation = showTranslation
        },

        setUser(state, user){
            state.user = user
        }
    }
})