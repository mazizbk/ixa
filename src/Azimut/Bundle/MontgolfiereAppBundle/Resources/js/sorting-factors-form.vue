<template>
    <bootstrap-modal
        :title="trans(isNew?'montgolfiere.backoffice.campaigns.sorting_factors.modal.new_factor.title':'montgolfiere.backoffice.campaigns.sorting_factors.modal.edit_factor.title')"
        @hidden="$emit('close')"
        @save="save"
        :saving="saving"
    >
        <form>
            <div class="form-group" v-for="language in campaignLanguages">
                <label :for="'name_'+language">
                    {{trans('montgolfiere.backoffice.campaigns.sorting_factors.name')}}
                    ({{trans('montgolfiere.backoffice.campaigns.locale.'+language)}})
                </label>
                <input type="text" class="form-control" :id="'name_'+language" v-model="form.names[language]" />
            </div>
        </form>
    </bootstrap-modal>
</template>

<script lang="ts">
    import {Component, Prop} from "vue-property-decorator";
    import Vue from "vue";
    import BootstrapModal from "./bootstrap-modal.vue";
    import {SortingFactor, Translatable} from "./store-sorting-factors";
    import {namespace} from "vuex-class";

    const sortingFactors = namespace('sortingFactors');

    @Component({components: {BootstrapModal}})
    export default class SortingFactorsForm extends Vue {
        @Prop(Array) private campaignLanguages!: string[];
        @Prop() private sortingFactor!: SortingFactor;
        @Prop(Number) private campaignId!: number;
        @sortingFactors.Action('add') private addSortingFactor!: ({campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) => Promise<void>;
        @sortingFactors.Action('update') private updateSortingFactor!: ({campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) => Promise<void>;
        private form = {names: {}};
        private isNew = false;
        private saving = false;

        beforeMount(): void {
            this.isNew = !this.sortingFactor;
            if(!this.isNew) {
                Object.assign(this.form.names, this.sortingFactor.names);
            }
            else {
                const names: Translatable = {};
                this.campaignLanguages.forEach((language: string) => {
                    names[language] = '';
                })
                this.form = {names};
            }
        }

        async save() {
            if(this.saving) {
                return;
            }
            this.saving = true;

            if(this.isNew) {
                await this.addSortingFactor({
                    campaignId: this.campaignId,
                    sortingFactor: {
                        names: this.form.names,
                    },
                });
            }
            else {
                await this.updateSortingFactor({
                    campaignId: this.campaignId,
                    sortingFactor: {
                        id: this.sortingFactor.id,
                        names: this.form.names,
                    },
                });
            }
            this.$emit('close');
        }
    }
</script>
