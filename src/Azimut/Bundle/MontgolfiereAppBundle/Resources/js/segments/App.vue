<template>
    <div>
        <vue-element-loading :active="loading" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
        <alert v-if="error" type="danger">
            <strong>Une erreur est survenue lors du chargement des segments</strong><br />
            {{error}}
        </alert>
        <div class="well">
            <div class="pull-left">
                <div class="checkbox"><label><input type="checkbox" v-model="showQuestion" /> Afficher les questions</label></div>
                <div class="checkbox" :class="{disabled:!showQuestion}"><label><input type="checkbox" v-model="showTags" :disabled="!showQuestion" /> Afficher les tags</label></div>
            </div>
            <div class="pull-right">
                <btn @click="newSegmentModal=true">
                    <i class="glyphicon glyphicon-plus text-primary"></i>
                    {{ 'montgolfiere.backoffice.campaigns.segments.new_segment'|trans }}
                </btn>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row" v-if="!loading">
            <div class="col-md-4" v-for="segment in segments" :key="segment.id">
                <Segment :campaign="campaign" :segment="segment" :show-question="showQuestion" :show-tags="showTags"></Segment>
            </div>
        </div>
        <modal v-model="newSegmentModal" :footer="false">
            <template #header>
                <button type="button" class="close" aria-label="Close" style="position: relative; z-index: 1060" @click="newSegmentModal=false">
                    <!-- 1060 is bigger than dialog z-index 1050 because it got cover by title sometimes -->
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <slot name="title">Nouveau segment</slot>
                </h4>
            </template>
            <SegmentForm v-if="newSegmentModal" :allowed-languages="campaign.allowedLanguages" @cancel="newSegmentModal=false" @done="newSegmentModal=false"></SegmentForm>
        </modal>
    </div>
</template>

<script lang="ts">
import {Action, Getter} from "vuex-class";
const VueElementLoading = require("vue-element-loading");
import {Component, Vue} from 'vue-property-decorator';
import {CAMPAIGN_REQ, SEGMENTS_REQ} from "./store";
import {Campaign, Segment as SegmentObj} from "./types";
import Segment from "./components/Segment.vue";
import SegmentForm from "./components/SegmentForm.vue";
@Component({
    components: {Segment, VueElementLoading, SegmentForm}
})
export default class App extends Vue {
    @Getter('segments') private segments!: SegmentObj[];
    @Action(CAMPAIGN_REQ) private getCampaign!: () => Promise<Campaign>;
    @Action(SEGMENTS_REQ) private getSegments!: (force?: boolean) => Promise<void>;
    private showQuestion = true;
    private showTags = false;
    private loading = true;
    private error: string|null = null;
    private newSegmentModal = false;
    private campaign: Campaign|null = null;

    async mounted() {
        try {
            [, this.campaign] = await Promise.all([this.getSegments(), this.getCampaign()]);
        }
        catch(e) {
            this.error = e.message;
            console.error(e);
        }
        finally {
            this.loading = false;
        }
    }

}
</script>
