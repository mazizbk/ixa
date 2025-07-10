<template>
    <div>
        <vue-element-loading :active="loading" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
        <div class="panel panel-default" :class="{'panel-danger': segment.disabled || !isSegmentValid(segment)}">
            <div class="panel-heading">
                <h4 class="panel-title">
                    {{ segment.name }} ({{ ('montgolfiere.backoffice.campaigns.locale.'+segment.locale)|trans }})
                    <span class="pull-right">
                        <a href="#" @click.prevent="showAutofillModal=true" v-tooltip="'Remplissage automatique'"><i class="glyphicon glyphicon-pro glyphicon-pro-magic"></i></a>
                        <a href="#" @click.prevent="editModal=true"><i class="glyphicon glyphicon-pencil"></i></a>
                        <a href="#" @click.prevent="deleteSegment" v-if="!segment.hasParticipations"><i class="glyphicon glyphicon-trash"></i></a>
                        <a href="#" v-tooltip="'Impossible de supprimer ce segment car des participations y sont associées'" v-else class="disabled"><i class="glyphicon glyphicon-trash"></i></a>
                    </span>
                </h4>
            </div>
            <div class="list-group">
                <Container @drop="onDrop" @drag-start="dragging=true" @drag-end="dragging=false" lock-axis="y">
                    <Draggable
                        class="list-group-item" :class="{'list-group-item-danger': step.type === 'item' && !step.question, 'list-group-item-warning': shouldWarnStep(step, i), 'list-group-item-gray': step.type==='divider'}"
                        v-for="(step, i) in segment.steps" :key="step.id"
                        @click.native.prevent="editStep=step; editStepModal=true"
                        :tag="{value:'a',props:{attrs:{href:'#'}}}"
                        v-tooltip="shouldWarnStep(step, i)?'Ce séparateur n\'est suivi d\'aucune question':undefined"
                    >
                        <span class="list-group-item-plus first" v-if="!dragging && i===0" @click.stop="createStep(1)">
                            <span class="list-group-item-plus-action">
                                <i class="glyphicon glyphicon-plus" style="color:#fff"></i>
                            </span>
                        </span>

                        <h4 class="list-group-item-heading">
                            <tooltip v-if="step.type==='item' || step.type==='question'" text="Question">
                                <i class="glyphicon glyphicon-question-sign" v-if="step.type==='item' || step.type==='question'"></i>
                            </tooltip>
                            <tooltip v-else-if="step.type==='divider'" text="Séparateur">
                                <i class="glyphicon glyphicon-pro glyphicon-pro-show-big-thumbnails"></i>
                            </tooltip>
                            <span v-if="step.question">{{step.question.label}}</span>
                            <span v-else-if="step.item">{{step.theme.name[segment.locale]}} - {{step.item.name[segment.locale]}}</span>
                            <span v-else-if="step.theme">{{step.theme.name[segment.locale]}}</span>
                        </h4>
                        <p class="list-group-item-text" v-if="showQuestion && step.question">
                            <span v-html="step.question.question"></span>
                            <template v-if="showTags" v-for="tag in step.question.tags">
                                <span class="label" :style="{backgroundColor:'#'+tag.color}">{{tag.name}}</span>
                                &#32;
                            </template>
                            <br />
                            <small v-html="step.question.description"></small>
                        </p>

                        <span class="list-group-item-plus" v-if="!dragging" @click.stop="createStep(step.position+1)">
                            <span class="list-group-item-plus-action">
                                <i class="glyphicon glyphicon-plus" style="color:#fff"></i>
                            </span>
                        </span>
                    </Draggable>
                </Container>
            </div>
        </div>
        <modal v-model="editModal" :footer="false">
            <template #header>
                <button type="button" class="close" aria-label="Close" style="position: relative; z-index: 1060" @click="editModal=false">
                    <!-- 1060 is bigger than dialog z-index 1050 because it got cover by title sometimes -->
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <slot name="title">Modification du segment {{segment.name}}</slot>
                </h4>
            </template>
            <SegmentForm
                v-if="editModal"
                :segment="segment" :allowed-languages="campaign.allowedLanguages"
                @cancel="editModal=false" @done="editModal=false"
            ></SegmentForm>
        </modal>
        <modal v-model="editStepModal" :footer="false">
            <template #header>
                <button type="button" class="close" aria-label="Close" style="position: relative; z-index: 1060" @click="editStepModal=false">
                    <!-- 1060 is bigger than dialog z-index 1050 because it got cover by title sometimes -->
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <slot name="title">
                        <span v-if="editStep && editStep.id">Modification d'une étape</span>
                        <span v-else>Création d'une étape</span>
                    </slot>
                </h4>
            </template>
            <EditStepModal v-if="editStepModal" :segment="segment" :step="editStep" @hide="editStepModal=false"></EditStepModal>
        </modal>
        <AutofillSegmentModal v-if="showAutofillModal" :segment="segment" @hide="showAutofillModal=false"></AutofillSegmentModal>
    </div>
</template>

<script lang="ts">
import {Component, Prop, Vue} from 'vue-property-decorator';
import {Container, Draggable, DropResult} from "vue-smooth-dnd";
import {Action} from "vuex-class";
import {SEGMENT_DELETE, STEP_MOVE} from "../store";
import {Campaign, Segment as SegmentObj, Step, StepType} from "../types";
import SegmentForm from "./SegmentForm.vue";
import EditStepModal from "./EditStepModal.vue";
import AutofillSegmentModal from "./AutofillSegmentModal.vue";

const VueElementLoading = require("vue-element-loading");

@Component({
    components: {EditStepModal, Container, Draggable, VueElementLoading, SegmentForm, AutofillSegmentModal}
})
export default class Segment extends Vue {
    @Prop({type: Object, required: true}) private segment!: SegmentObj;
    @Prop({type: Object, required: true}) private campaign!: Campaign;
    @Prop({type: Boolean, default: false}) private showQuestion!: boolean;
    @Prop({type: Boolean, default: false}) private showTags!: boolean;
    @Action(SEGMENT_DELETE) private doDeleteSegment!: (segment: SegmentObj) => Promise<void>;
    @Action(STEP_MOVE) private moveStep!: (payload: {segment: SegmentObj, step: Step, newPosition: number}) => Promise<void>;
    private loading = false;
    private dragging = false;
    // Edit segment
    private editModal = false;
    private showAutofillModal = false;

    private editStepModal = false;
    private editStep: Partial<Step>|null = null;

    private async onDrop(dropResult: DropResult|null) {
        if(!dropResult || dropResult.removedIndex === null || dropResult.addedIndex === null || dropResult.removedIndex === dropResult.addedIndex) {
            return;
        }

        const step = this.segment.steps[dropResult.removedIndex];
        if(!step) {
            return;
        }
        this.loading = true;
        try {
            await this.moveStep({segment: this.segment, step: step, newPosition: dropResult.addedIndex+1});
        }
        finally {
            this.loading = false;
        }
    }

    private async deleteSegment() {
        if(confirm(`Êtes-vous certain de vouloir supprimer le segment ${this.segment.name} ?`)) {
            this.loading = true;
            try {
                await this.doDeleteSegment(this.segment);
            }
            finally {
                this.loading = false;
            }
        }
    }

    private createStep(position: number) {
        this.editStep = {
            position
        };
        this.editStepModal = true;
    }

    private shouldWarnStep(step: Step, i: number): boolean {
        return step.type === 'divider' && (!this.segment.steps[i+1] || this.segment.steps[i+1].type === 'divider');
    }

    private isSegmentValid(segment: SegmentObj): boolean {
        for (const step of segment.steps) {
            if((step.type === StepType.question || step.type === StepType.item) && !step.question) {
                return false;
            }
        }

        return true;
    }
}
</script>

<style lang="scss" scoped>

.list-group-item-gray {
    background-color: #F0F0F0;
}

.list-group-item.smooth-dnd-draggable-wrapper {
    overflow: visible !important;
}
.list-group-item-plus {
    $height: 8px;
    position: absolute;
    z-index: 10;
    left: 0;
    bottom: -1 * $height / 2;
    width: 100%;
    height: $height;
    background: #FF7900;
    border-radius: 2px;
    opacity: 0;
    &:hover {
        opacity: 1;
    }
    &.first {
        top: -1 * $height / 2;
    }

    .list-group-item-plus-action {
        $action-dimension: 40px;
        position: absolute;
        z-index: 10;
        background: #FF7900;
        right: -22px;
        bottom: -18px;
        width: $action-dimension;
        height: $action-dimension;
        border-radius: 50% 50% 5% 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        transform: rotate(135deg);
        > * {
            transform: rotate(-135deg);
        }
    }
}
.disabled {
    cursor: not-allowed;
}
</style>
