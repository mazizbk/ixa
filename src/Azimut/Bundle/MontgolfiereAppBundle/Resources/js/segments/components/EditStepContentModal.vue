<template>
    <div>
        <form>
            <div class="form-group">
                <label for="stepType">Type</label>
                <select v-model="form.type" class="form-control" id="stepType">
                    <option v-for="type in types" :value="type.type">{{type.name}}</option>
                </select>
            </div>
            <div class="form-group" v-if="form.type!==StepType.question">
                <label for="stepTheme">Thème</label>
                <select v-model="form.theme" class="form-control" id="stepTheme">
                    <option v-for="theme in themes" :value="theme.id">{{theme.name[segment.locale]}}</option>
                </select>
            </div>
            <div class="form-group" v-if="form.type===StepType.item">
                <label for="stepItem">Item</label>
                <select v-model="form.item" class="form-control" id="stepItem" :class="{disabled:!form.theme}">
                    <option v-if="form.theme" v-for="item in items" :value="item.id">{{item.name[segment.locale]}}</option>
                </select>
            </div>
            <div class="form-group" v-if="form.type && form.type!==StepType.divider">
                <label>Question</label>
                <p class="form-control-static">
                    <span v-if="question">
                        {{question.label}}<br />
                        {{question.question}}
                    </span>
                    <span v-else>Pas de question définie</span>
                    <br />
                    <btn size="xs" type="primary" @click="displayChangeQuestionModal=true">Modifier la question</btn>
                    <EditStepQuestionModal v-if="displayChangeQuestionModal" :step="step" :type="form.type" @hide="displayChangeQuestionModal=false" @select-question="changeQuestion"></EditStepQuestionModal>
                </p>
            </div>
            <hr>
            <div class="text-right">
                <btn type="default" @click="$emit('hide')">Annuler</btn>
                <btn type="primary" @click="save">
                    <vue-element-loading :active="saving" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
                    Enregistrer
                </btn>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {Item, Question, Segment, Step, StepType, Theme} from "../types";
import {Action, Getter} from "vuex-class";
import {STEP_CREATE, STEP_UPDATE, THEMES_REQ} from "../store";
import VueElementLoading from "vue-element-loading";
import EditStepQuestionModal from "./EditStepQuestionModal.vue";

@Component({components:{VueElementLoading, EditStepQuestionModal}})
export default class EditStepContentModal extends Vue {
    @Prop({required: true, type: Object}) private segment!: Segment;
    @Prop({required: true, type: Object}) private step!: Step;
    @Getter('themes') private themes!: Theme[];
    @Action(THEMES_REQ) private loadThemes!: (force?: boolean) => Promise<Theme[]>;
    @Action(STEP_CREATE) private doCreateStep!: (payload: {segment: Segment, step: any}) => Promise<void>;
    @Action(STEP_UPDATE) private doUpdateStep!: (payload: {segment: Segment, step: Step, payload: any}) => Promise<void>;

    private form: {type?: StepType, theme?: number, item?: number, question?: number} = {};
    private types = [
        {type: StepType.divider, name: 'Séparateur'},
        {type: StepType.item, name: 'Question centrale'},
        {type: StepType.question, name: 'Question annexe'},
    ];
    private items: Item[] = [];
    private question: Question|null = null;
    private StepType = StepType;
    private saving = false;
    private displayChangeQuestionModal = false;
    private loading = true;

    public async mounted() {
        this.form = {
            type: this.step.type,
            theme: this.step.theme?.id,
            item: this.step.item?.id,
            question: this.step.question?.id,
        };
        this.question = this.step.question || null;
        await this.loadThemes();
        this.loading = false;
    }

    @Watch('form.theme', {immediate: true})
    private themeChange(themeId: number): void {
        const theme = this.themes.find((theme: Theme) => theme.id === themeId);
        this.items = !theme ? [] : theme.items;
        if(!this.loading) {
            this.form.item = undefined;
        }
    }

    @Watch('form.item')
    private itemChange(): void {
        if(this.loading) {
            return;
        }
        this.form.question = undefined;
        this.question = null;
    }

    private async changeQuestion(question: Question) {
        this.question = question;
        this.form.question = question.id;
    }

    private async save() {
        if(this.saving) {
            return;
        }
        this.saving = true;
        try {
            if(!this.step.id) {
                await this.doCreateStep({segment: this.segment, step: {...this.form, position: this.step.position}});
            }
            else {
                await this.doUpdateStep({segment: this.segment, step: this.step, payload: this.form});
            }
            this.$emit('hide');
        }
        finally {
            this.saving = false;
        }
    }
}
</script>
