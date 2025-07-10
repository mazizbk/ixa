import Vue from "vue";
import Vuex from 'vuex';
import {Campaign, Question, Segment, Step, Theme} from "./types";
import {axios} from "./index";

Vue.use(Vuex);

export const CAMPAIGN_REQ = 'CAMPAIGN_REQ';

export const SEGMENTS_REQ = 'SEGMENT_REQ';
const SEGMENTS_SUCCESS = 'SEGMENTS_SUCCESS';
export const SEGMENT_CREATE = 'SEGMENT_CREATE';
export const SEGMENT_SAVE = 'SEGMENT_SAVE';
export const SEGMENT_DELETE = 'SEGMENT_DELETE';

export const STEP_MOVE = 'STEP_MOVE';
const STEP_MOVE_SUCCESS = 'STEP_MOVE_SUCCESS';
export const STEP_CREATE = 'STEP_CREATE';
const STEP_CREATE_SUCCESS = 'STEP_CREATE_SUCCESS';
export const STEP_UPDATE = 'STEP_UPDATE';
const STEP_UPDATE_SUCCESS = 'STEP_UPDATE_SUCCESS';
export const STEP_DELETE = 'STEP_DELETE';
const STEP_DELETE_SUCCESS = 'STEP_DELETE_SUCCESS';

export const THEMES_REQ = 'THEMES_REQ';
const THEMES_REQ_SUCCESS = 'THEMES_REQ_SUCCESS';

export const QUESTIONS_REQ = 'QUESTIONS_REQ';
const QUESTIONS_REQ_SUCCESS = 'QUESTIONS_REQ_SUCCESS';

interface RootState {
    segments: Segment[];
    themes: Theme[];
    questions: Question[];
}

export default new Vuex.Store<RootState>({
    state: {
        segments: [],
        themes: [],
        questions: [],
    },
    mutations: {
        [SEGMENTS_SUCCESS]: (state, segments: Segment[]) => {
            state.segments = segments;
        },
        [SEGMENT_CREATE]: (state, segment: Segment) => {
            state.segments.push(segment);
        },
        [SEGMENT_SAVE]: (state, segment: Segment) => {
            const index = state.segments.findIndex((testedSegment: Segment) => testedSegment.id === segment.id);
            if(index < 0) {
                state.segments.push(segment);
            }
            else {
                state.segments.splice(index, 1, segment);
            }
        },
        [SEGMENT_DELETE]: (state, segment: Segment) => {
            const index = state.segments.indexOf(segment);
            if(index < 0) {
                return;
            }
            state.segments.splice(index, 1);
        },
        [STEP_MOVE_SUCCESS]: (state, {segment, step, newPosition}: {segment: Segment, step: Step, newPosition: number}) => {
            const segmentIndex = state.segments.indexOf(segment);
            if(segmentIndex === -1) {
                return;
            }
            state.segments[segmentIndex].steps.splice(segment.steps.indexOf(step), 1);
            // steps are 0-indexed as it is an array, but positions are 1-indexed
            state.segments[segmentIndex].steps.splice(newPosition-1, 0, step);
        },
        [STEP_CREATE_SUCCESS]: (state, {segment, step}: {segment: Segment, step: Step}) => {
            const segmentIndex = state.segments.indexOf(segment);
            if(segmentIndex === -1) {
                return;
            }
            state.segments[segmentIndex].steps.splice(step.position-1, 0, step);
        },
        [STEP_UPDATE_SUCCESS]: (state, {segment, step, updatedStep}: {segment: Segment, step: Step, updatedStep: Step}) => {
            const segmentIndex = state.segments.indexOf(segment);
            if(segmentIndex === -1) {
                return;
            }
            state.segments[segmentIndex].steps.splice(state.segments[segmentIndex].steps.indexOf(step), 1, updatedStep);
        },
        [STEP_DELETE_SUCCESS]: (state, {segment, step}: {segment: Segment, step: Step}) => {
            const segmentIndex = state.segments.indexOf(segment);
            if(segmentIndex === -1) {
                return;
            }
            state.segments[segmentIndex].steps.splice(segment.steps.indexOf(step), 1);
        },
        [THEMES_REQ_SUCCESS]: (state, themes: Theme[]) => {
            state.themes = themes;
        },
        [QUESTIONS_REQ_SUCCESS]: (state, questions: Question[]) => {
            state.questions = questions;
        },
    },
    getters: {
        segments: (state) => state.segments,
        themes: (state) => state.themes,
        questions: (state) => state.questions,
    },
    actions: {
        [CAMPAIGN_REQ]: async (): Promise<Campaign> => {
            return (await axios.get<Campaign>('.')).data;
        },
        [SEGMENTS_REQ]: async ({state, commit}, force?: boolean): Promise<void> => {
            if(state.segments && state.segments.length > 0 && !force) {
                return;
            }
            const response = await axios.get<Segment[]>('segments');
            commit(SEGMENTS_SUCCESS, response.data);
        },
        [SEGMENT_CREATE]: async({commit}, segment: Segment): Promise<Segment> => {
            const response = await axios.post<Segment>('segments/new', segment);
            commit(SEGMENT_CREATE, response.data);

            return response.data;
        },
        [SEGMENT_SAVE]: async({commit}, segment: Segment): Promise<Segment> => {
            if(!segment.id) {
                throw new Error('Segment to save has no ID');
            }
            const response = await axios.post<Segment>(`segments/${segment.id}`, {
                name: segment.name,
                disabled: segment.disabled,
                locale: segment.locale,
            });
            commit(SEGMENT_SAVE, response.data);

            return response.data;
        },
        [SEGMENT_DELETE]: async({commit}, segment: Segment): Promise<void> => {
            if(!segment.id) {
                return;
            }
            await axios.delete(`segments/${segment.id}/delete`);
            commit(SEGMENT_DELETE, segment);
        },
        [STEP_MOVE]: async({state, commit, dispatch}, {segment, step, newPosition}: {segment: Segment, step: Step, newPosition: number}): Promise<void> => {
            const oldPosition = segment.steps.indexOf(step) + 1;
            // Update value locally before sending to server to maintain interface in state (user dropped step in some place, leave it there for now)
            commit(STEP_MOVE_SUCCESS, {segment, step, newPosition});

            try {
                await axios.patch(`segment/${segment.id}/step/${step.id}`, {position: newPosition});
            }
            catch(e) {
                // Revert step to its initial position
                commit(STEP_MOVE_SUCCESS, {segment, step, newPosition: oldPosition});
            }
            finally {
                // Force reload of segments
                await dispatch(SEGMENTS_REQ, true);
            }
        },
        [STEP_CREATE]: async({commit}, {segment, step}: {segment: Segment, step: Step}): Promise<void> => {
            const response = await axios.post<Step>(`segment/${segment.id}/step`, step);
            commit(STEP_CREATE_SUCCESS, {segment, step: response.data});
        },
        [STEP_UPDATE]: async({commit}, {segment, step, payload}: {segment: Segment, step: Step, payload: any}): Promise<void> => {
            const response = await axios.patch<Step>(`segment/${segment.id}/step/${step.id}`, payload);
            commit(STEP_UPDATE_SUCCESS, {segment, step, updatedStep: response.data});
        },
        [STEP_DELETE]: async({state, commit, dispatch}, {segment, step}: {segment: Segment, step: Step}): Promise<void> => {
            try {
                await axios.delete(`segment/${segment.id}/step/${step.id}`);
                commit(STEP_DELETE_SUCCESS, {segment, step});
            }
            finally {
                // Force reload of segments
                await dispatch(SEGMENTS_REQ, true);
            }
        },
        [THEMES_REQ]: async({state, commit}, force?: boolean): Promise<Theme[]> => {
            if(state.themes && state.themes.length > 0 && !force) {
                return state.themes;
            }
            const response = await axios.get<Theme[]>('themes');
            commit(THEMES_REQ_SUCCESS, response.data);

            return response.data;
        },
        [QUESTIONS_REQ]: async({state, commit}, force?: boolean): Promise<Question[]> => {
            if(state.questions && state.questions.length > 0 && !force) {
                return state.questions;
            }
            const response = await axios.get<Question[]>('questions');
            commit(QUESTIONS_REQ_SUCCESS, response.data);

            return response.data;
        },
    },
});
