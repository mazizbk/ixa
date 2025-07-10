import 'vue-class-component/hooks';
import Vue from 'vue';
import App from './automatic-affectations-app.vue';
import {SymfonyTranslatorPlugin} from "./vue-sf-translator";

Vue.use(SymfonyTranslatorPlugin);

let attributesBackups: NamedNodeMap|null = null;

(new Vue({
    render: function(h) {return h(App, {props: (this as any).nodeProperties()})},
    methods: {
        // This help with getting the props on #app directly as props of App
        nodeProperties() {
            let props: {[key: string]: any} = {};
            if(!attributesBackups) {
                attributesBackups = this.$el.attributes;
            }
            Array.prototype.forEach.call(attributesBackups, (node) => {
                let value = node.value;
                if(/^\d+$/.test(value)) {
                    value = parseInt(value);
                }
                else if(/^([\[{])/.test(value)) {
                    value = JSON.parse(value);
                }
                props[node.name] = value;
            });
            return props;
        },
    },
})).$mount('#app');

// Ignores HMR for this file because we don't know how to
// Ignoring HMR here allows HMR to work for *.vue files
if(module.hot) {
    module.hot.accept('./automatic-affectations.ts');
}
