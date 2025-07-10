declare module 'uiv' {
    import Vue, {PluginFunction, PluginObject, VueConstructor} from "vue";
    // import Vue, {PluginFunction} from "vue";
    // const uiv: PluginFunction<Vue>;

    // class Uiv {
    //     static install: PluginFunction<{}>;
    // }

    // enum TRIGGERS {
    //     CLICK = 'click',
    //     HOVER = 'hover',
    //     FOCUS = 'focus',
    //     HOVER_FOCUS = 'hover-focus',
    //     OUTSIDE_CLICK = 'outside-click',
    //     MANUAL = 'manual',
    // }
    //
    // class VTooltip extends Vue {
    //     text: string;
    //     trigger?: TRIGGERS;
    // }

    interface UivConstructor extends PluginFunction<{}>{}
    const uiv: UivConstructor;

    export = uiv;
}
