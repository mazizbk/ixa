<template>
   <div>
       <form>
           <div class="form-group">
               <label for="segmentName">Nom</label>
               <input type="text" class="form-control" id="segmentName" v-model="form.name" />
           </div>
           <div class="form-group">
               <label>
                   <input type="checkbox" v-model="form.disabled" />
                   Désactiver le segment
               </label>
           </div>
           <div class="form-group">
               <label for="segmentLocale">Langue</label>
               <select class="form-control" id="segmentLocale" v-model="form.locale">
                   <option v-for="locale in allowedLanguages" :value="locale">{{('montgolfiere.backoffice.campaigns.locale.'+locale)|trans}}</option>
               </select>
           </div>
           <hr>
           <div class="text-right">
               <btn type="default" @click="$emit('cancel')">Annuler</btn>
               <btn type="primary" @click="save">
                   <vue-element-loading :active="saving" spinner="spinner" background-color="#FFFFFF99" color="#FF7900"></vue-element-loading>
                   Enregistrer
               </btn>
           </div>
       </form>
   </div>
</template>

<script lang="ts">
import {Component, Prop, Vue} from "vue-property-decorator";
import {Segment} from "../types";
import {Action} from "vuex-class";
import {SEGMENT_CREATE, SEGMENT_SAVE} from "../store";
import VueElementLoading from "vue-element-loading";

@Component({components:{VueElementLoading}})
export default class SegmentForm extends Vue {
    @Prop({type: Array, required: true}) allowedLanguages!: string[];
    @Prop(Object) segment: Segment|undefined;
    @Action(SEGMENT_CREATE) private segmentCreate!: (segment: Segment) => Promise<Segment>;
    @Action(SEGMENT_SAVE) private segmentSave!: (segment: Segment) => Promise<Segment>;
    private form = {
        name: '',
        disabled: false,
        locale: '',
    };
    private saving = false;

    public mounted() {
        if(!this.segment) {
            this.form.locale = this.allowedLanguages[0];
            return;
        }
        this.form.name = this.segment.name;
        this.form.disabled = this.segment.disabled;
        this.form.locale = this.segment.locale;
    }

    public async save() {
        if(this.saving) {
            return;
        }
        this.saving = true;
        try {
            if(!this.segment) {
                await this.segmentCreate(this.form as Segment);
            }
            else {
                await this.segmentSave({...this.form as Segment, id: this.segment.id});
            }
            this.$emit('done');
        }
        finally {
            this.saving = false;
        }
    }
}
</script>

<style scoped>

</style>
