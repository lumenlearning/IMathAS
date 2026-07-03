<template>
  <div class="home">
    <div class="assess-header headerpane">
      <div style="flex-grow: 1">
        <h1>{{ $t('work-add') }}: {{ ainfo.name }}</h1>
      </div>
      <timer v-if="ainfo.showwork_cutoff > 0"
        :total="ainfo.showwork_cutoff * 60"
        :end="ainfo.showwork_local_cutoff_expires"
        :grace="0">
      </timer>
      <div>
        <button @click = "save" class="primary">
          {{ saveLabel }}
        </button>
      </div>
    </div>
    <div v-if="readyToShow">
      <p v-if="ainfo.showwork_cutoff > 0">
        {{ $t('work-duein', {date: ainfo.showwork_cutoff_expires_disp}) }}
      </p>
      <p v-if="questions.length === 0">
        {{ $t('work-noquestions') }}
      </p>
      <div v-if="showSingleShowwork">
        <p>{{ $t('work-all') }}</p>
        <showwork-input
            id="swgen"
            qn="gen"
            :value = "genwork"
            rows = "20"
            @input = "(val) => workChanged('gen', val)"
          />
      </div>
      <div v-for="(question,curqn) in questions" :key="curqn">
        <full-question-header
          :qn = "curqn"
          :showretry="false"
          v-if = "question.html !== null || question.showwork&2"
        />
        <question
          v-if = "question.html !== null"
          :qn = "curqn"
          :key="'sq'+curqn"
          :active = "true"
          :disabled = "true"
          :getwork = "(question.showwork&2)==2 ? 2 : 0"
          @workchanged = "(val) => workChanged(curqn, val)"
        />
        <div v-else-if="question.showwork&2">
          <showwork-input
            :id="'sw' + curqn"
            :qn="curqn"
            :value = "question.work"
            rows = "3"
            @input = "(val) => workChanged(curqn, val)"
          />
        </div>
      </div>
      <div>
        <button @click = "save" class="primary">
          {{ saveLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { store, actions } from '@/basicstore';
import Question from '@/components/question/Question.vue';
import FullQuestionHeader from '@/components/FullQuestionHeader.vue';
import ShowworkInput from '@/components/ShowworkInput.vue';
import Timer from '@/components/Timer.vue';

export default {
  name: 'ShowWork',
  components: {
    Question,
    FullQuestionHeader,
    ShowworkInput,
    Timer
  },
  data: function () {
    return {
      loaded: false,
      duringAssess: false,
      work: {}
    };
  },
  computed: {
    ainfo () {
      return store.assessInfo;
    },
    mode () {
      return store.inAssess ? 'aftertake' : 'gb';
    },
    readyToShow () {
      return ((this.mode === 'gb' && store.assessInfo.hasOwnProperty('questions')) ||
        (this.mode === 'aftertake' && store.assessInfo.hasOwnProperty('score')));
    },
    hasScore () {
      return store.assessInfo.hasOwnProperty('score');
    },
    questions () {
      var out = {};
      for (var qn in store.assessInfo.questions) {
        if ((store.assessInfo.questions[qn].showwork & 2) || this.showSingleShowwork) {
          out[qn] = store.assessInfo.questions[qn];
        }
      }
      return out;
    },
    saveLabel () {
      return store.inAssess ? this.$t('work-save_continue') : this.$t('work-save');
    },
    showSingleShowwork () {
      return ((store.assessInfo.singleshowwork & 8) &&  // single showwork
              (store.assessInfo.singleshowwork & 2));   // after
    },
    genwork () {
      return store.assessInfo.swgen ?? '';
    }
  },
  methods: {
    loadScoresIfNeeded () {
      if (this.mode === 'gb' && !this.readyToShow) {
        // for when it's accessed from gradebook
        actions.getQuestions();
      } else if (this.mode === 'aftertake' && !this.readyToShow) {
        // for when it's accessed after by-assess submission
        actions.getScores();
      }
    },
    workChanged (qn, value) {
      store.work[qn] = value;
    },
    save () {
      actions.submitWork();
    }
  },
  created () {
    this.loadScoresIfNeeded();
  },
  updated () {
    this.loadScoresIfNeeded();
  }
};
</script>

<style>
</style>
