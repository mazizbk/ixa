import {ActionTree, Module} from "vuex";
import {axios} from "./sorting-factors";

const SET = 'SET';
const ADD = 'ADD';
const UPDATE = 'UPDATE';
const REMOVE = 'REMOVE';
const ADD_VALUE = 'ADD_VALUE';
const UPDATE_VALUE = 'UPDATE_VALUE';
const REMOVE_VALUE = 'REMOVE_VALUE';
const MOVE_VALUE = 'MOVE_VALUE';

type StateType = {
    cache: {[key: number]: SortingFactor[]};
};

const state: StateType = {
    cache: {},
};
const mutations = {
    [SET](state: StateType, {campaignId, data}: {campaignId: number, data: SortingFactor[]}) {
        state.cache[campaignId] = data;
    },
    [ADD](state: StateType, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            state.cache[campaignId] = [];
        }
        if(!sortingFactor.hasOwnProperty('values')) {
            sortingFactor.values = [];
        }
        state.cache[campaignId].push(sortingFactor);
    },
    [UPDATE](state: StateType, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            state.cache[campaignId] = [sortingFactor];
            return;
        }
        const index = state.cache[campaignId].findIndex((testedSortingFactor: SortingFactor) => testedSortingFactor.id === sortingFactor.id);
        state.cache[campaignId].splice(index, 1, sortingFactor);
    },
    [REMOVE](state: StateType, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const index = state.cache[campaignId].indexOf(sortingFactor);
        if(index<0) {
            return;
        }
        state.cache[campaignId].splice(index, 1);
    },
    [ADD_VALUE](state: StateType, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const factorIndex = state.cache[campaignId].indexOf(sortingFactor);
        if(factorIndex<0) {
            return;
        }
        state.cache[campaignId][factorIndex].values!.push(value);
    },
    [UPDATE_VALUE](state: StateType, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const factorIndex = state.cache[campaignId].indexOf(sortingFactor);
        if(factorIndex<0) {
            return;
        }
        const valueIndex = state.cache[campaignId][factorIndex].values!.findIndex((testedValue: SortingFactorValue) => testedValue.id === value.id);
        if(valueIndex<0) {
            return;
        }

        state.cache[campaignId][factorIndex].values!.splice(valueIndex, 1, value);
    },
    [REMOVE_VALUE](state: StateType, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const factorIndex = state.cache[campaignId].indexOf(sortingFactor);
        if(factorIndex<0) {
            return;
        }
        const valueIndex = state.cache[campaignId][factorIndex].values!.indexOf(value);
        if(valueIndex<0) {
            return;
        }

        state.cache[campaignId][factorIndex].values!.splice(valueIndex, 1);
    },
    [MOVE_VALUE](state: StateType, {campaignId, sortingFactor, value, position}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue, position: -1|1}) {
        if(!state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const factorIndex = state.cache[campaignId].indexOf(sortingFactor);
        if(factorIndex<0) {
            return;
        }
        const values = state.cache[campaignId][factorIndex].values!;
        const valueIndex = values.indexOf(value);
        if(valueIndex<0) {
            return;
        }
        if((valueIndex === 0 && position === -1) || valueIndex === values.length && position === 1) {
            return;
        }

        if(position === -1) {
            values[valueIndex-1].position!++;
            values[valueIndex].position!--;
        }
        else {
            values[valueIndex+1].position!--;
            values[valueIndex].position!++;
        }
        // Resort array makes it easier for displaying and upcoming mutations
        values.sort((a: SortingFactorValue, b: SortingFactorValue) => a.position! - b.position!);
    }
};
const getters = {
    getForCampaign: (state: StateType) => (id: number) => state.cache[id],
};
const actions: ActionTree<StateType, any> = {
    async warmCache({ commit, state }, campaignId: number) {
        if (state.cache.hasOwnProperty(campaignId)) {
            return;
        }
        const commitData = {
            campaignId,
            data: (await axios.get<SortingFactor[]>(Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors', {id: campaignId}))).data,
        }

        commit(SET, commitData);
    },
    async add({commit, state}, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        const url = Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_new', {id: campaignId});
        const sf = await axios.post<SortingFactor[]>(url, {campaign_sorting_factor: sortingFactorToForm(sortingFactor)});

        commit(ADD, {campaignId, sortingFactor: sf.data});
    },
    async update({commit, state}, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        const url = Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_edit', {id: campaignId, sorting_factor: sortingFactor.id!});
        const sf = await axios.put<SortingFactor[]>(url, {campaign_sorting_factor: sortingFactorToForm(sortingFactor)});

        commit(UPDATE, {campaignId, sortingFactor: sf.data});
    },
    async remove({commit, state}, {campaignId, sortingFactor}: {campaignId: number, sortingFactor: SortingFactor}) {
        await axios.delete<SortingFactor[]>(Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_delete', {id: campaignId, sorting_factor: sortingFactor.id!}));

        commit(REMOVE, {campaignId, sortingFactor});
    },
    async addValue({commit, state}, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        const url = Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_value_new', {id: campaignId, sorting_factor: sortingFactor.id!});
        value = (await axios.post<SortingFactorValue>(url, {campaign_sorting_factor_value: sortingFactorValueToForm(value)})).data;

        commit(ADD_VALUE, {campaignId, sortingFactor, value});
    },
    async updateValue({commit, state}, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        const url = Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_value_edit', {id: campaignId, sorting_factor: sortingFactor.id!, value: value.id!});
        value = (await axios.put<SortingFactorValue>(url, {campaign_sorting_factor_value: sortingFactorValueToForm(value)})).data;

        commit(UPDATE_VALUE, {campaignId, sortingFactor, value});
    },
    async removeValue({commit, state}, {campaignId, sortingFactor, value}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue}) {
        await axios.delete(Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_value_delete', {id: campaignId, sorting_factor: sortingFactor.id!, value: value.id!}));

        commit(REMOVE_VALUE, {campaignId, sortingFactor, value});
    },
    async moveValue({commit, state}, {campaignId, sortingFactor, value, position}: {campaignId: number, sortingFactor: SortingFactor, value: SortingFactorValue, position: -1|1}) {
        const url = Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_sorting_factors_value_edit', {id: campaignId, sorting_factor: sortingFactor.id!, value: value.id!});
        await axios.patch(url, {campaign_sorting_factor_value: {position: value.position!+position}});

        commit(MOVE_VALUE, {campaignId, sortingFactor, value, position});
    },
};

function sortingFactorToForm(sortingFactor: SortingFactor) {
    const data: {[key: string]: string;} = {};
    Object.entries(sortingFactor.names).forEach(([locale, name]) => {
        data['name_'+locale] = name;
    });

    return data;
}

function sortingFactorValueToForm(value: SortingFactorValue) {
    const data: {[key: string]: string|number;} = {
        workforce: value.workforce
    };
    Object.entries(value.labels).forEach(([locale, label]) => {
        data['label_'+locale] = label;
    });

    return data;
}

export const store: Module<typeof state, any> = {
    namespaced: true,
    state,
    mutations,
    getters,
    actions,
}

export interface Translatable {
    [key: string]: string;
}

export interface SortingFactor {
    id?: number;
    names: Translatable;
    values?: SortingFactorValue[];
}

export interface SortingFactorValue {
    id?: number;
    labels: Translatable;
    workforce: number;
    position?: number;
}
