import _Vue from "vue";

export function SymfonyTranslatorPlugin(Vue: typeof _Vue): void {
    Vue.prototype.trans = Translator.trans.bind(Translator);
    Vue.filter('trans', Translator.trans.bind(Translator));
}
