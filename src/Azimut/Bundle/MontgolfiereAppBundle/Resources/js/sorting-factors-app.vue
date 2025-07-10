<template>
    <div>
        <div class="pull-right">
            <a href="#" class="btn btn-default" style="margin-bottom: 20px;" @click.prevent="createNewSortingFactor=true">
                <i class="glyphicon glyphicon-plus"></i>
                {{ trans('montgolfiere.backoffice.campaigns.sorting_factors.new_factor') }}
            </a>
        </div>
        <div class="clearfix"></div>
        <div>
            <div class="row">
                <div class="col-md-4" v-for="sortingFactor in sortingFactors">
                    <div class="panel panel-default" :class="{'panel-danger': deletingFactors.includes(sortingFactor), deleting: deletingFactors.includes(sortingFactor)}">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <span v-for="(locale, index) in campaignAllowedLanguages">
                                    <strong>
                                        {{ trans('montgolfiere.backoffice.campaigns.locale.'+locale) }} :
                                    </strong>
                                    {{ sortingFactor.names[locale]||'' }}
                                    <br v-if="index !== campaignAllowedLanguages.length-1">
                                </span>
                                <span class="pull-right">
                                    <a href="#" @click.prevent="createNewSortingFactorValue=sortingFactor"><i class="glyphicon glyphicon-plus"></i></a>
                                    <a href="#" @click.prevent="editingSortingFactor=sortingFactor"><i class="glyphicon glyphicon-pencil"></i></a>
                                    <a href="#" @click.prevent="deleteFactor(sortingFactor)"><i class="glyphicon glyphicon-trash"></i></a>
                                </span>
                            </h4>
                        </div>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>{{ trans('montgolfiere.backoffice.campaigns.sorting_factors.values.labels') }}</th>
                                <th>{{ trans('montgolfiere.backoffice.campaigns.sorting_factors.values.workforce') }}</th>
                                <th class="icon-column"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(value, index) in sortingFactor.values" :class="{deleting: deletingFactorsValues.includes(value)}">
                                <td>
                                    <span v-for="(name, locale, index) in value.labels">
                                        <strong>{{ trans('montgolfiere.backoffice.campaigns.locale.'+locale) }}</strong>
                                        {{ name }}
                                        <br v-if="index !== value.labels.length-1">
                                    </span>
                                </td>
                                <td>{{ value.workforce }}</td>
                                <td class="icon-column">
                                    <a href="#" @click.prevent="moveSortingFactorValue({campaignId, sortingFactor, value, position: -1})" :class="{disabled: index===0}"><i class="glyphicon glyphicon-arrow-up"></i></a>
                                    <a href="#" @click.prevent="moveSortingFactorValue({campaignId, sortingFactor, value, position: 1})" :class="{disabled: index===sortingFactor.values.length-1}"><i class="glyphicon glyphicon-arrow-down"></i></a>
                                    <a href="#" @click.prevent="updateSortingFactorValue=value"><i class="glyphicon glyphicon-pencil"></i></a>
                                    <a href="#" @click.prevent="deleteFactorValue(sortingFactor, value)"><i class="glyphicon glyphicon-trash"></i></a>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td class="text-right">{{ trans('montgolfiere.backoffice.campaigns.sorting_factors.values.workforce_total') }}</td>
                                <td>{{sortingFactor.values.reduce((previousValue, currentValue) => previousValue+currentValue.workforce, 0)}}</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <sorting-factors-value-form :campaign-languages="campaignAllowedLanguages" :campaign-id="campaignId" :sorting-factor="sortingFactor" v-if="createNewSortingFactorValue===sortingFactor" @close="createNewSortingFactorValue=null"></sorting-factors-value-form>
                    <sorting-factors-value-form :campaign-languages="campaignAllowedLanguages" :campaign-id="campaignId" :sorting-factor="sortingFactor" :value="updateSortingFactorValue" v-if="sortingFactor.values.includes(updateSortingFactorValue)" @close="updateSortingFactorValue=null"></sorting-factors-value-form>
                </div>
            </div>
        </div>
        <sorting-factors-form :campaign-languages="campaignAllowedLanguages" :campaign-id="campaignId" v-if="createNewSortingFactor" @close="createNewSortingFactor=null"></sorting-factors-form>
        <sorting-factors-form :campaign-languages="campaignAllowedLanguages" :campaign-id="campaignId" :sorting-factor="editingSortingFactor" v-if="editingSortingFactor" @close="editingSortingFactor=null"></sorting-factors-form>
    </div>
</template>

<script lang="ts">
    /// <reference path="translator.d.ts" />
    import Vue from 'vue';
    import {Component, Prop} from "vue-property-decorator";
    import {namespace} from 'vuex-class';
    import {SortingFactor, SortingFactorValue} from "./store-sorting-factors";
    import SortingFactorsForm from "./sorting-factors-form.vue";
    import SortingFactorsValueForm from "./sorting-factors-value-form.vue";

    const sortingFactors = namespace('sortingFactors');

    @Component({components: {SortingFactorsForm, SortingFactorsValueForm}})
    export default class App extends Vue {
        @Prop(Number) private campaignId!: number;
        @Prop(String) private allowedLanguagesStr!: string;
        @sortingFactors.Getter("getForCampaign") private getCampaignSortingFactor!: (campaignId: number) => SortingFactor[];
        @sortingFactors.Action("remove") private removeCampaignSortingFactor!: ({campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) => Promise<void>;
        @sortingFactors.Action("removeValue") private removeCampaignSortingFactorValue!: ({campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) => Promise<void>;
        private campaignAllowedLanguages: string[] = [];
        private sortingFactors: SortingFactor[] = [];
        private deletingFactors: SortingFactor[] = [];
        private deletingFactorsValues: SortingFactorValue[] = [];
        private createNewSortingFactor = false;
        private editingSortingFactor: SortingFactor|null = null;

        private createNewSortingFactorValue: SortingFactor|null = null // The SortingFactor for which we're creating a new value
        private updateSortingFactorValue: SortingFactorValue|null = null;
        @sortingFactors.Action("moveValue") private moveSortingFactorValue!: ({campaignId, sortingFactor, value, position}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue, position: number}) => Promise<void>;

        async mounted() {
            this.campaignAllowedLanguages = this.allowedLanguagesStr.split(',');
            await this.$store.dispatch('sortingFactors/warmCache', this.campaignId);
            this.sortingFactors = this.getCampaignSortingFactor(this.campaignId);
        }
        async deleteFactor(sortingFactor: SortingFactor) {
            if(this.deletingFactors.includes(sortingFactor)) {
                return;
            }
            if(!confirm(Translator.trans('montgolfiere.backoffice.campaigns.sorting_factors.confirmation.sorting_factor_delete'))) {
                return;
            }
            this.deletingFactors.push(sortingFactor);
            try {
                await this.removeCampaignSortingFactor({campaignId: this.campaignId, sortingFactor});
            }
            finally {
                this.deletingFactors.splice(this.deletingFactors.indexOf(sortingFactor), 1);
            }
        }
        async deleteFactorValue(sortingFactor: SortingFactor, value: SortingFactorValue) {
            if(this.deletingFactorsValues.includes(value)) {
                return;
            }
            if(!confirm(Translator.trans('montgolfiere.backoffice.campaigns.sorting_factors.confirmation.sorting_factor_value_delete'))) {
                return;
            }
            this.deletingFactorsValues.push(value);
            try {
                await this.removeCampaignSortingFactorValue({campaignId: this.campaignId, sortingFactor, value});
            }
            finally {
                this.deletingFactorsValues.splice(this.deletingFactorsValues.indexOf(value), 1);
            }
        }
    }
</script>

<style lang="css" scoped>
    .deleting {
        pointer-events: none;
        user-select: none;
        opacity: .4;
    }
</style>
