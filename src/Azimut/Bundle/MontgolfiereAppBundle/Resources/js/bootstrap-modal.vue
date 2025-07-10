<template>
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><slot name="title"><h4 class="modal-title">{{title}}</h4></slot></div>
                <div class="modal-body">
                    <slot></slot>
                </div>
                <div class="modal-footer">
                    <slot name="footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" :disabled="saving">
                            {{trans('montgolfiere.backoffice.common.cancel')}}
                        </button>
                        <button type="button" class="btn btn-primary" @click="$emit('save')" :disabled="saving">
                            <i class="fa fa-spin fa-spinner" v-show="saving"></i>
                            {{trans('montgolfiere.backoffice.common.submit')}}
                        </button>
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, Prop} from "vue-property-decorator";
    import Vue from "vue";
    declare function $(el: any): any;

    @Component
    export default class BootstrapModal extends Vue {
        @Prop() private title!: string;
        @Prop(Boolean) private saving!: boolean;

        mounted(): void {
            $(this.$el).modal().on('hidden.bs.modal', () => {this.$emit('hidden');});
        }
        async beforeDestroy(): Promise<void> {
            $(this.$el).modal('hide');
        }
    }
</script>
