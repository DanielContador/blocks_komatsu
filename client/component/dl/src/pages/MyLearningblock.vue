<template>
  <div>
    <div v-for="(block, index) in blocks" :key="index" v-if="block.displayCondition">
      <p v-if="isBlockLoaded(block.design)" class="tui-dlCourseCategoryHeading">
        {{ block.label }}<i class="ml-4 fa-light fa-chevron-right"></i>
      </p>
      <div class="tui-courseCatalogGrid">
        <SliderGridRow :columns="1" :design="block.design" v-bind="block.params"
                       @loaded="throwHandler(block.handler)"/>
      </div>
    </div>
  </div>
</template>

<script>
import SliderGridRow from '../components/slider/SliderGridRow.vue';

export default {
  components: {
    SliderGridRow
  },
  props: ['props'],
  data() {
    return {
      inprogressLabel: '',
      notprogressLabel: '',
      completedLabel: '',
      programsLabel: '',
      completedCoursesBlockLabel: '',
      gridInProgress: 'gridInProgress',
      gridCourses: 'gridCourses',
      gridCompleted: 'gridCompleted',
      gridPrograms: 'gridPrograms',
      gridNotProgress: 'gridNotProgress',
      showInProgressHeading: false,
      showNotProgressHeading: false,
      showCompletedHeading: false,
      showProgramsHeading: false,
      programscourses: false,
      completedprograms: false,
      notProgressBlock: false,
      completedCoursesBlock: false,
      programsBlock: false,
      coursesBlock: false,
      onProgressBlock: false,
      blocks: [{
        displayCondition: '',
        label: '',
        design: '',
        handler: '',
        params: {}
      }]
    };
  },
  created() {
    // Parsear los datos JSON recibidos como props
    const data = JSON.parse(this.props);
    this.inprogressLabel = data.inprogress_label;
    this.notprogressLabel = data.notprogresscourses_label;
    this.completedLabel = data.completed_label;
    this.programsLabel = data.programs_label;
    this.completedCoursesBlockLabel = data.completedcoursesblock_label;
    this.programscourses = data.showprogramscourses;
    this.completedprograms = data.showcompletedprograms;
    this.notProgressBlock = data.notprogressblock;
    this.completedCoursesBlock = data.completedcoursesblock;
    this.programsBlock = data.programsblock;
    this.coursesBlock = data.coursesblock;
    this.onProgressBlock = data.onprogressblock;
    this.blocks = data.data
  },
  methods: {
    throwHandler(handlerName) {
      switch (handlerName) {
        case 'handleInProgressLoaded':
          this.showInProgressHeading = true;
          break
        case 'handleNotProgressLoaded':
          this.showNotProgressHeading = true;
          break
        case 'handleCompletedLoaded':
          this.showCompletedHeading = true;
          break
        case 'handleProgramsLoaded':
          this.showProgramsHeading = true;
          break
        default:
          break
      }
    },
    isBlockLoaded(design) {
      switch (design) {
        case 'gridInProgress':
          return this.showInProgressHeading;
        case 'gridNotProgress':
          return this.showNotProgressHeading;
        case 'gridCompleted':
          return this.showCompletedHeading;
        case 'gridPrograms':
          return this.showProgramsHeading;
        default:
          break
      }
    }
  }
};
</script>

<style lang="scss">
@media(min-width: 1024px) {
  hr {
    position: relative;
    margin-right: -78px !important;
    margin-bottom: 35px !important;
  }
}
</style>
