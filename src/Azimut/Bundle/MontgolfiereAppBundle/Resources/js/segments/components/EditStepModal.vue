<template>
    <div v-if="!editContentsMode">
        <vue-element-loading :active="loading" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
        <p>
            Etape {{step.position}}<br />
            <span v-if="step.type==='divider'">Séparateur pour le thème <span v-if="step.theme">{{step.theme.name[segment.locale]}}</span><span v-else>Aucun thème</span></span>
        </p>
        <p>
            Que voulez-vous faire ?
        </p>
        <div class="list-group">
            <a href="#" class="list-group-item" @click.prevent="editContentsMode=true">
                <h4 class="list-group-item-heading">
                    <i class="glyphicon glyphicon-pencil"></i>
                    Modifier le contenu de l'étape
                </h4>
            </a>
            <a href="#" class="list-group-item" v-if="step.type !== StepType.divider" @click.prevent="displayChangeQuestionModal=true">
                <h4 class="list-group-item-heading">
                    <i class="glyphicon glyphicon-question-sign"></i>
                    <span v-if="step.question">Changer la question</span>
                    <span v-else>Choisir la question</span>
                </h4>
                <p class="list-group-item-text" v-if="step.question">
                    {{step.question.label}}<br />
                  <span v-html="step.question.question"></span><br />
                    <small v-html="step.question.description"></small>
                </p>
            </a>
            <a href="#" class="list-group-item list-group-item-danger" @click.prevent="deleteStep">
                <h4 class="list-group-item-heading">
                    <i class="glyphicon glyphicon-trash"></i>
                    Supprimer l'étape
                </h4>
            </a>
            <a href="#" class="list-group-item" @click.prevent="$emit('hide')">
                <h4 class="list-group-item-heading">
                    <i class="glyphicon glyphicon-times"></i>
                    Annuler
                </h4>
            </a>
        </div>
        <EditStepQuestionModal v-if="displayChangeQuestionModal" :step="step" :type="step.type" @hide="displayChangeQuestionModal=false" @select-question="changeQuestion"></EditStepQuestionModal>
    </div>
    <EditStepContentModal v-else :segment="segment" :step="step" @hide="$emit('hide')"></EditStepContentModal>
</template>

<script lang="ts">
import {Component, Prop, Vue} from "vue-property-decorator";
import {Question, Segment, Step, StepType} from "../types";
import {Action} from "vuex-class";
import {STEP_CREATE, STEP_DELETE, STEP_UPDATE} from "../store";
import VueElementLoading from "vue-element-loading";
import EditStepContentModal from "./EditStepContentModal.vue";
import EditStepQuestionModal from "./EditStepQuestionModal.vue";

@Component({components:{EditStepQuestionModal, EditStepContentModal, VueElementLoading}})
export default class EditStepModal extends Vue {
    @Prop({required: true, type: Object}) private step!: Step;
    @Prop({required: true, type: Object}) private segment!: Segment;
    @Action(STEP_DELETE) private doDeleteStep!: (payload: {segment: Segment, step: Step}) => Promise<void>;
    @Action(STEP_UPDATE) private doUpdateStep!: (payload: {segment: Segment, step: Step, payload: any}) => Promise<void>;
    private loading = false;
    private editContentsMode = false;
    private displayChangeQuestionModal = false;
    private StepType = StepType;

    public mounted() {
        if(!this.step.id) {
            // Creating a new step, open edit contents directly
            this.editContentsMode = true;
        }
    }

    private async deleteStep() {
        if(confirm('Êtes-vous certain de vouloir supprimer cette étape ?')) {
            this.loading = true;
            try {
                await this.doDeleteStep({segment: this.segment, step: this.step});
                this.$emit('hide');
            }
            finally {
                this.loading = false;
            }
        }
    }

    private async changeQuestion(question: Question) {
        this.loading = true;
        try {
            await this.doUpdateStep({segment: this.segment, step: this.step, payload: {question: question.id}});
            this.$emit('hide');
        }
        finally {
            this.loading = false;
        }
    }
}
</script>

