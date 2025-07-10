<template>
    <modal v-model="shown" :footer="false" size="lg" append-to-body ref="modal" @hide="$emit('hide')">
        <template #header>
            <button type="button" class="close" aria-label="Close" style="position: relative; z-index: 1060" @click="$emit('hide')">
                <!-- 1060 is bigger than dialog z-index 1050 because it got cover by title sometimes -->
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <slot name="title">Remplissage automatique de segment</slot>
            </h4>
        </template>
        <vue-element-loading :active="loading" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
        <div class="well form-inline">
            Merci de sélectionner le tag à utiliser pour remplir les étape du segment<br />
            <div class="form-group">
                <label for="filterTag">Tag</label>
                <select id="filterTag" v-model="selectedTag" class="form-control">
                    <option value=""></option>
                    <option v-for="tag in tags" :value="tag">{{tag.name}}</option>
                </select>
            </div>
            <label>
                <input type="checkbox" v-model="overrideExisting" /> Remplacer les questions déjà remplies
            </label>
            <div class="pull-right">
                <a href="#" class="btn btn-success" @click.prevent="save" :class="{active: saving}">
                    <span v-if="saving">
                        <i class="fa fa-spin fa-spinner"></i>
                        Enregistrement
                        {{ savingDone }} / {{ savingTotal }}
                    </span>
                    <span v-else>Enregistrer</span>
                </a>
            </div>
        </div>
        <div style="overflow: auto; height: calc(100vh - 255px)">
            <table class="table table-striped table-hover table-head-sticky">
                <thead>
                <tr>
                    <th>Etape</th>
                    <th>Type</th>
                    <th style="width:80%">Question</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="step in segment.steps">
                        <td>{{ step.position }}</td>
                        <td v-if="step.type === 'divider'">Séparateur</td>
                        <td v-else-if="step.type === 'item'">Question centrale</td>
                        <td v-else-if="step.type === 'question'">Question annexe</td>
                        <td v-else>{{step.type}}</td>
                        <td v-if="selectedQuestions[step.id]">
                            <div v-html="selectedQuestions[step.id].description"></div>

                            <template v-for="tag in selectedQuestions[step.id].tags">
                                <span class="label" :style="{backgroundColor:'#'+tag.color}">{{tag.name}}</span>
                                &#32;
                            </template>
                        </td>
                        <td v-else class="disabled">
                            Inchangée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </modal>
</template>

<script lang="ts">
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import VueElementLoading from "vue-element-loading";
import {Question, QuestionTag, Segment, Step} from "../types";
import {Action, Getter} from "vuex-class";
import {QUESTIONS_REQ, STEP_UPDATE} from "../store";

@Component({components:{VueElementLoading}})
export default class AutofillSegmentModal extends Vue {
    @Prop({required: true, type: Object}) private segment!: Segment;
    @Action(QUESTIONS_REQ) private loadQuestions!: (force?: boolean) => Promise<Question[]>;
    @Getter('questions') private questions!: Question[];
    @Action(STEP_UPDATE) private doUpdateStep!: (payload: {segment: Segment, step: Step, payload: any}) => Promise<void>;
    private tags: QuestionTag[] = [];
    private shown = true;
    private loading = true;
    private selectedTag: QuestionTag|null = null;
    private overrideExisting = true;
    private selectedQuestions: (Question|null)[] = [];
    private saving = false;
    private savingDone = 0;
    private savingTotal = 0;

    public async mounted() {
        const modal = this.$refs.modal as Vue;
        modal.$el.querySelector<HTMLElement>('.modal-content')!.style.height = 'calc(100vh - 60px)';
        modal.$el.querySelector<HTMLElement>('.modal-dialog')!.style.width = 'calc(100vw - 100px)';
        try {
            await this.loadQuestions();
            const tags: QuestionTag[] = [];
            this.questions.forEach((question: Question) => {
                question.tags.forEach((tag: QuestionTag) => {
                    if(!tags.some((testedTag: QuestionTag) => tag.id === testedTag.id)) {
                        tags.push(tag);
                    }
                });
            });
            this.tags = tags;
        }
        finally {
            this.loading = false;
        }
    }

    public async save() {
        if(this.saving) {
            return;
        }

        this.saving = true;
        this.savingDone = 0;
        this.savingTotal = Object.values(this.selectedQuestions).length;
        const promises = [];

        for(const stepId in this.selectedQuestions) {
            const question = this.selectedQuestions[stepId]!;
            if(!question) {
                this.savingDone++;
                continue;
            }
            const step = this.getStep(parseInt(stepId));
            promises.push(this.doUpdateStep({segment: this.segment, step, payload: {question: question.id}}).then(() => this.savingDone++));
        }
        await Promise.all(promises);
        this.$emit('hide');
    }

    @Watch('selectedTag')
    @Watch('overrideExisting')
    public onTagChange() {
        this.selectedQuestions = [];
        for(const step of this.segment.steps) {
            if(step.type !== 'item') {
                continue;
            }
            if(!this.overrideExisting && step.question) {
                continue;
            }
            this.selectedQuestions[step.id] = this.selectQuestion(step.item?.id!);
        }
    }

    private selectQuestion(stepId: number) {
        for(const question of this.questions) {
            if(!question.item || question.item.id !== stepId) {
                continue;
            }
            for(const tag of question.tags) {
                if(tag.id === this.selectedTag!.id) {
                    return question;
                }
            }
        }

        return null;
    }

    private getStep(stepId: number): Step {
        for(const step of this.segment.steps) {
            if(step.id === stepId) {
                return step;
            }
        }
        throw new Error('Could not find step '+stepId);
    }
}
</script>

<style lang="less" scoped>

</style>
