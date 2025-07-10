<template>
    <bootstrap-modal
        :title="trans(isNew?'montgolfiere.backoffice.campaigns.sorting_factors.modal.new_value.title':'montgolfiere.backoffice.campaigns.sorting_factors.modal.edit_value.title')"
        @hidden="$emit('close')"
        @save="save"
        :saving="saving"
    >
        <form>
            <div class="form-group" v-for="language in campaignLanguages">
                <label :for="'label_'+language">
                    {{trans('montgolfiere.backoffice.campaigns.sorting_factors.values.label')}}
                    ({{trans('montgolfiere.backoffice.campaigns.locale.'+language)}})
                </label>
                <input type="text" class="form-control" :id="'label_'+language" v-model="form.labels[language]" />
            </div>
            <div class="form-group">
                <label for="workforce">{{trans('montgolfiere.backoffice.campaigns.sorting_factors.values.workforce')}}</label>
                <input type="number" class="form-control" min="0" id="workforce" v-model="form.workforce" />
            </div>
        </form>
    </bootstrap-modal>
</template>

<script lang="ts">
    import {Component, Prop} from "vue-property-decorator";
    import Vue from "vue";
    import BootstrapModal from "./bootstrap-modal.vue";
    import {SortingFactor, SortingFactorValue, Translatable} from "./store-sorting-factors";
    import {namespace} from "vuex-class";

    const sortingFactors = namespace('sortingFactors');

    @Component({components: {BootstrapModal}})
    export default class SortingFactorsValueForm extends Vue {
        @Prop(Array) private campaignLanguages!: string[];
        @Prop() private sortingFactor!: SortingFactor;
        @Prop(Number) private campaignId!: number;
        @Prop() private value!: SortingFactorValue|null;
        @sortingFactors.Action('addValue') private addSortingFactorValue!: ({campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) => Promise<void>;
        @sortingFactors.Action('updateValue') private updateSortingFactorValue!: ({campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) => Promise<void>;
        private form: {labels: Translatable, workforce: number} = {labels: {}, workforce: 0};
        private isNew = false;
        private saving = false;

        beforeMount(): void {
            this.isNew = !this.value;
            if(!this.isNew) {
                Object.assign(this.form.labels, this.value!.labels);
                this.form.workforce = this.value!.workforce;
            }
            else {
                const labels: Translatable = {};
                this.campaignLanguages.forEach((language: string) => {
                    labels[language] = '';
                })
                this.form.labels = labels;
            }
        }

        async save() {
            if(this.saving) {
                return;
            }
            this.saving = true;

            try {

                if(this.isNew) {
                    await this.addSortingFactorValue({
                        campaignId: this.campaignId,
                        sortingFactor: this.sortingFactor,
                        value: this.form,
                    });
                }
                else {
                    await this.updateSortingFactorValue({
                        campaignId: this.campaignId,
                        sortingFactor: this.sortingFactor,
                        value: {...this.form, id: this.value!.id},
                    });
                }
                this.$emit('close');
            }
            finally {
                this.saving = false;
            }
        }
    }
</script>
