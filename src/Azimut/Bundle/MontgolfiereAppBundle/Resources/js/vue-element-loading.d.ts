declare module 'vue-element-loading' {
    import Vue from 'vue';

    export default class VueElementLoading extends Vue {
        props: {
            active: boolean;
            spinner: string;
            color: string;
            backgroundColor: string;
            size: string;
            duration: string;
            delay: number|string;
            isFullScreen: boolean;
            text: string;
            // textStyle: {};
        }
    }
}
