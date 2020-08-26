import Vue from 'vue';
import Vuex from 'vuex';
import post from './modules/post';
import profile from './modules/profile';
import settings from './modules/settings';
import tag from './modules/tag';
import topic from './modules/topic';
import user from './modules/user';

Vue.use(Vuex);

export const store = new Vuex.Store({
    modules: {
        post,
        profile,
        settings,
        tag,
        topic,
        user,
    },
});