import 'vue-class-component/hooks';
import Vue from 'vue';
import Vuex from 'vuex';
import axiosBase from 'axios';
import VueAxios from "vue-axios";
import App from './sorting-factors-app.vue';
import {SymfonyTranslatorPlugin} from "./vue-sf-translator";
import { store as sortingFactors } from "./store-sorting-factors";

export const axios = axiosBase.create({
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

Vue.use(Vuex);
Vue.use(VueAxios, axios);
Vue.use(SymfonyTranslatorPlugin);

const store = new Vuex.Store({
    modules: {
        sortingFactors
    },
    strict: process.env.NODE_ENV !== 'production',
});

let attributesBackups: NamedNodeMap|null = null;

(new Vue({
    render: function(h) {return h(App, {props: (this as any).nodeProperties()})},
    store,
    methods: {
        // This help with getting the props on #app directly as props of App
        nodeProperties() {
            let props: {[key: string]: any} = {};
            if(!attributesBackups) {
                attributesBackups = this.$el.attributes;
            }
            Array.prototype.forEach.call(attributesBackups, (node) => {
                props[node.name] = /^\d+$/.test(node.value)?parseInt(node.value):node.value;
            });
            return props;
        },
    },
})).$mount('#app');

// Ignores HMR for this file because we don't know how to
// Ignoring HMR here allows HMR to work for *.vue files
if(module.hot) {
    module.hot.accept('./sorting-factors.ts');
}
