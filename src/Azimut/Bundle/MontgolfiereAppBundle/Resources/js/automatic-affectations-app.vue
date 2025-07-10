<template>
    <div>
        <div class="well form-inline">
            <div class="form-group form-group-margin" v-for="(sortingFactor, i) in sortingFactors">
                <label :for="'filter_sorting_factor_'+sortingFactor.id">{{sortingFactor.names[selectedLocale]}} :</label>
                <select :id="'filter_sorting_factor_'+sortingFactor.id" v-model="filters[i]" class="form-control">
                    <option></option>
                    <option v-for="value in sortingFactor.values" :value="value.id">{{value.labels[selectedLocale]}}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_locale">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.locale')}}</label>
                <select id="filter_locale" v-model="selectedLocale" class="form-control">
                    <option v-for="locale in locales" :value="locale">{{trans('montgolfiere.backoffice.campaigns.locale.'+locale)}}</option>
                </select>
            </div>
            <div class="btn-group btn-group-xs">
                <a @click.prevent="setAll('yes')" class="btn btn-info">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.enable_all')}}</a>
                <a @click.prevent="setAll('no')" class="btn btn-info">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.disable_all')}}</a>
            </div>
            <div>
                {{trans('montgolfiere.backoffice.campaigns.automatic_affectations.combination_count')}} {{filteredCombinationCount}}
                <div v-if="filteredCombinationCount>2500">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.combination_limit_reached')}}</div>
            </div>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th v-for="sortingFactor in sortingFactors">{{sortingFactor.names[selectedLocale]}}</th>
                <th>{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.combination')}}</th>
            </tr>
            </thead>
            <tbody v-if="filteredCombinationCount<2500">
            <tr v-for="combination in combinations" v-if="displayCombination(combination)">
                <td v-for="j in sortingFactors.length">{{sortingFactorValues[Array.isArray(combination)?combination[j-1]:combination].labels[selectedLocale]}}</td>
                <td>
                    <select class="form-control" v-model="affectationsCopy[combination.join('-')+'-'+selectedLocale]">
                        <option value="no">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.impossible')}}</option>
                        <option value="yes">{{trans('montgolfiere.backoffice.campaigns.automatic_affectations.possible')}}</option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="pull-right"><a href="#" @click.prevent="save" class="btn btn-primary">{{trans('montgolfiere.backoffice.common.save')}}</a></div>
    </div>
</template>
<script lang="ts">
    import Vue from "vue";
    import {Component, Prop, Watch} from "vue-property-decorator";

    @Component
    export default class App extends Vue {
        @Prop(Number) private campaignId!: number;
        @Prop(Array) private locales!: string[];
        @Prop(Array) private sortingFactors!: SortingFactor[];
        @Prop(Object) private affectations!: Affectations;
        private selectedLocale: string|null = null;
        private combinations: any[] = [];
        private sortingFactorValues: {[key: number]: SortingFactorValue|undefined} = {};
        private filters: {[key: number]: number|string} = [];
        private filteredCombinationCount = 0;
        private affectationsCopy: Affectations = {};

        beforeMount() {
            this.selectedLocale = this.locales[0];
            const allPossibleValues: number[][] = [];
            this.sortingFactors.forEach((sortingFactor: SortingFactor) => {
                allPossibleValues.push(sortingFactor.values.map((value: SortingFactorValue) => value.id));
                sortingFactor.values.forEach(((value: SortingFactorValue) => {
                    this.sortingFactorValues[value.id] = value;
                }));
            });
            this.combinations = allPossibleCombinations(allPossibleValues.reverse())!;
            for(const combination of this.combinations) {
                for(const locale of this.locales) {
                    const key = combination.join('-')+'-'+locale;
                    this.$set(this.affectationsCopy, key, this.affectations.hasOwnProperty(key) ? 'yes' : 'no');
                }
            }
            this.updateFilteredCombinationCount();
        }

        save() {
            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'data';
            input.value = JSON.stringify(Object.entries(this.affectationsCopy).filter(value => value[1] === 'yes').map(value => value[0]));
            form.appendChild(input);

            const button = document.createElement('button');
            button.type = 'submit';
            form.appendChild(button);

            this.$el.appendChild(form);
            form.submit();
        }

        displayCombination(combination: number[]): boolean {
            if(Object.keys(this.filters).length === 0) {
                return true;
            }

            for(const [key, value] of Object.entries(this.filters)) {
                if(value !== '' && value !== undefined && value !== null && combination[key as any as number] !== value) {
                    return false;
                }
            }

            return true;
        }

        setAll(status: 'yes' | 'no'): void {
            Object.values(this.combinations).filter(this.displayCombination).forEach((key: number[]) => {
                this.affectationsCopy[(key).join('-')+'-'+this.selectedLocale] = status;
            });
        }

        @Watch('filters')
        updateFilteredCombinationCount() {
            this.filteredCombinationCount = Object.values(this.combinations).filter(this.displayCombination).length;
        }
    }

    // https://stackoverflow.com/a/53314650/2898156
    function allPossibleCombinations(items: any[], isCombination=false): any[]|undefined {
        // finding all possible combinations of the last 2 items
        // remove those 2, add these combinations
        // isCombination shows if the last element is itself part of the combination series
        if(items.length === 1){
            return items[0];
        }
        else if(items.length === 2){
            var combinations = []
            for (var i=0; i<items[1].length; i++){
                for(var j=0; j<items[0].length; j++){
                    let combination: any|any[];
                    if(isCombination){
                        // clone array to not modify original array
                        combination = items[1][i].slice();
                        combination.push(items[0][j]);
                    }
                    else{
                        combination = [items[1][i], items[0][j]];
                    }
                    combinations.push(combination);
                }
            }
            return combinations
        }
        else if(items.length > 2){
            var last2 = items.slice(-2);
            var butLast2 = items.slice(0, items.length - 2);
            last2 = allPossibleCombinations(last2, isCombination)!;
            butLast2.push(last2);

            return allPossibleCombinations(butLast2, true)
        }
    }

    interface SortingFactor {
        id: number;
        names: TranslatableString;
        values: SortingFactorValue[];
    }

    interface SortingFactorValue {
        id: number;
        labels: TranslatableString;
    }

    interface Affectations {
        [key: string]: number|string|boolean|undefined;
    }

    interface TranslatableString {
        [key: string]: string;
    }
</script>

<style scoped>
    .form-group-margin {
        margin-right: 10px;
    }
</style>
