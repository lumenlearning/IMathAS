<template>
  <div class="questionwrap questionpane">
    <div v-if="showheader" class="full-question-header">
      <h2 class="inlineheader" style="margin: 0">{{ $t('work-add') }}</h2>
    </div>
    <p>{{ $t('work-all') }}</p>
    <showwork-input
      id="swgen"
      qn="gen"
      :value = "genwork"
      rows = "20"
      @input = "updateWork"
      @blur = "workChanged"
      @focus = "workFocused"
    />
    <div>
      <button @click = "save" :class="submitClass">
        {{ $t('work-save') }}
      </button>
    </div>
    <div style="margin-top:20px">
      <button
        v-if = "showSubmit"
        type = "button"
        class = "primary"
        @click = "submitAssess"
      >
        {{ $t('header-assess_submit') }}
      </button>
    </div>
  </div>
</template>

<script>
import { store, actions } from '@/basicstore';
import ShowworkInput from '@/components/ShowworkInput.vue';

export default {
  name: 'ShowworkSingle',
  components: {
    ShowworkInput,
  },
  data: function () {
    return {
      work: '',
      lastWorkVal: ''
    };
  },
  props: ['showheader'],
  computed: {
    submitClass () {
      return (store.assessInfo.submitby === 'by_assessment')
        ? 'secondary'
        : 'primary';
    },
    genwork () {
      return store.assessInfo.swgen;
    },
    showSubmit () {
      return store.assessInfo.displaymethod === 'skip' &&
            store.assessInfo.submitby === 'by_assessment' &&
            (this.work != '' && this.work != '<p></p>');
    }
  },
  methods: {
    updateWork (val) {
      this.work = val;
    },
    workFocused () {
      this.lastWorkVal = this.work;
    },
    workChanged () {
      // changed - cue for autosave
      if (this.work !== this.lastWorkVal) {
        store.work['gen'] = this.work;
        // this component only used "during", 
        // so autosave value
        actions.doAutosave('gen', 'sw', 0);
      }
    },
    save () {
      // since autosave records work, just use autosave to get in-progress notes
      actions.doAutosave('gen', 'sw', 0);
      actions.submitAutosave(true);
    },
    submitAssess () {
      actions.submitAssessment();
    },
  },
  mounted: function () {
    console.log('mounted');
    this.work = store.assessInfo.swgen;
  }
};
</script>

<style>
</style>
