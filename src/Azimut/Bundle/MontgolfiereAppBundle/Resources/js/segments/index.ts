import axiosBase from "axios";
import * as uiv from 'uiv';
import Vue from "vue";
import VueAxios from "vue-axios";
import {SymfonyTranslatorPlugin} from "../vue-sf-translator";
import store from './store';
import App from "./App.vue";

export const axios = axiosBase.create({
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

Vue.use(VueAxios, axios);
Vue.use(uiv);
Vue.use(SymfonyTranslatorPlugin);

(new Vue({
    render: function(h) {return h(App)},
    store,
})).$mount('#segmentsApp');

// Ignores HMR for this file because we don't know how to
// Ignoring HMR here allows HMR to work for *.vue files
if(module.hot) {
    module.hot.accept('./index.ts');
}
