<template>
    <modal v-model="shown" :footer="false" size="lg" append-to-body ref="modal" @hide="$emit('hide')">
        <template #header>
            <button type="button" class="close" aria-label="Close" style="position: relative; z-index: 1060" @click="$emit('hide')">
                <!-- 1060 is bigger than dialog z-index 1050 because it got cover by title sometimes -->
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <slot name="title">Sélection de question</slot>
            </h4>
        </template>
        <vue-element-loading :active="loading" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
        <div class="well form-inline">
            <div class="form-group">
                <label for="filterQuery">Question</label>
                <input type="text" id="filterQuery" v-model="filters.query" class="form-control" />
            </div>
            <div class="form-group">
                <label for="filterTag">Tag</label>
                <select id="filterTag" v-model="filters.tag" class="form-control">
                    <option value=""></option>
                    <option v-for="tag in tags" :value="tag.id">{{tag.name}}</option>
                </select>
            </div>
            <div class="form-group" v-if="type === StepType.question">
                <label for="filterType">Type</label>
                <select id="filterType" v-model="filters.type" class="form-control">
                    <option value=""></option>
                    <option v-for="type in QuestionType" :value="type" v-if="!isNaN(Number(type))">{{('montgolfiere.backoffice.questions.types.'+type)|trans}}</option>
                </select>
            </div>
            <label>
                <input type="checkbox" v-model="detailedMode" /> Mode détaillé
            </label>
        </div>
        <div style="overflow: auto; height: calc(100vh - 255px)">
            <table class="table table-striped table-hover table-head-sticky">
                <thead>
                <tr>
                    <th>Label</th>
                    <th v-if="detailedMode">Question</th>
                    <th v-if="detailedMode">Accroche</th>
                    <th v-if="type === StepType.question">Type</th>
                    <th v-if="detailedMode">Libellé gauche</th>
                    <th v-if="detailedMode">Libellé centre</th>
                    <th v-if="detailedMode">Libelle droite</th>
                    <th>Tags</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="question in compatibleQuestions" :key="question.id" :class="{success:step.question && question.id===step.question.id}">
                    <td>
                        <a href="#" @click.prevent="selectQuestion(question)"><strong>{{question.label}}</strong></a>
                    </td>
                    <td v-if="detailedMode" v-html="question.question"></td>
                    <td v-if="detailedMode" v-html="question.description"></td>
                    <td v-if="type === StepType.question">{{('montgolfiere.backoffice.questions.types.'+question.type)|trans}}</td>
                    <td v-if="detailedMode" v-html="question.leftLabel"></td>
                    <td v-if="detailedMode" v-html="question.centerLabel"></td>
                    <td v-if="detailedMode" v-html="question.rightLabel"></td>
                    <td>
                        <template v-for="tag in question.tags">
                            <span :key="tag.id" class="label label-default" :style="{'background-color':'#'+tag.color}">{{tag.name}}</span>{{ ' ' }}
                        </template>
                    </td>
                    <td>
                        <tooltip text="Sélectionner cette question" placement="left">
                            <a href="#" @click.prevent="selectQuestion(question)">
                                <span class="glyphicon glyphicon-copy"></span>
                            </a>
                        </tooltip>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </modal>
</template>

<script lang="ts">
import {Action, Getter} from "vuex-class";
import VueElementLoading from "vue-element-loading";
import {Component, Prop, Vue} from 'vue-property-decorator';
import {Question, QuestionTag, QuestionType, Step, StepType} from "../types";
import {QUESTIONS_REQ} from "../store";

@Component({components:{VueElementLoading}})
export default class EditStepQuestionModal extends Vue {
    @Prop({required: true, type: Object}) private step!: Step;
    @Prop({required: true, type: String}) private type!: StepType;
    @Action(QUESTIONS_REQ) private loadQuestions!: (force?: boolean) => Promise<Question[]>;
    @Getter('questions') private questions!: Question[];
    private shown = true;
    private loading = true;
    private detailedMode = true;
    private tags: QuestionTag[] = [];
    private filters: {tag: number|null, query: string, type: QuestionType|string} = {
        tag: null,
        query: '',
        type: '',
    };
    private StepType = StepType;
    private QuestionType = QuestionType;

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

    private get compatibleQuestions(): Question[] {
        let questions = this.questions;
        if(this.step.type === StepType.item) {
            questions = questions.filter((testedQuestion: Question) => testedQuestion.item?.id === this.step.item?.id);
        }
        else {
            questions = questions.filter((testedQuestion: Question) => testedQuestion.item===undefined);
        }

        if(this.filters.query) {
            questions = questions.filter((testedQuestion: Question) =>
                testedQuestion.label?.toLowerCase().indexOf(this.filters.query.toLowerCase()) > -1 ||
                testedQuestion.question?.toLowerCase().indexOf(this.filters.query.toLowerCase()) > -1 ||
                testedQuestion.description?.toLowerCase().indexOf(this.filters.query.toLowerCase()) > -1
            )
        }
        if(this.filters.tag) {
            questions = questions.filter((testedQuestion: Question) => testedQuestion.tags.some((testedTag: QuestionTag) => testedTag.id === this.filters.tag));
        }
        if(this.filters.type !== '') {
            questions = questions.filter((testedQuestion: Question) => testedQuestion.type === this.filters.type);
        }

        return questions;
    }

    private selectQuestion(question: Question) {
        this.$emit('select-question', question);
        this.$emit('hide');
    }
}
</script>
