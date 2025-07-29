<template>
  <div>
    <div class="tui-chartContainer" ref="chartContainer">
      <div class="tui-chartItem">
        <!-- Gráfico de Competencias: solo dos valores -->
        <h3 class="tui-titleGraph">{{ $str('title_competencies', 'block_dlreportgraph') }}</h3>
        <PieChart
            :labels="[$str('accredited', 'block_dlreportgraph'), $str('not_accredited', 'block_dlreportgraph')]"
            :dataValues="[competencies.accredited || 0, competencies.notAccredited || 0]"
            :labelPosition="labelPosition"
            aria-label-title="Competencias Acreditadas vs Sin Acreditar"
        />
      </div>
      <div class="tui-chartItem">
        <!-- Gráfico de Cursos -->
        <h3 class="tui-titleGraph">{{ $str('title_courses_competencies', 'block_dlreportgraph') }}</h3>
        <PieChart
            :labels="[$str('approved', 'block_dlreportgraph'),$str('failed', 'block_dlreportgraph'), $str('noInformation', 'block_dlreportgraph')]"
            :dataValues="[
            courses.failedByAttendance || 0, 
            courses.approved || 0, 
            courses.noInformation || 0
          ]"
            :labelPosition="labelPosition"
            aria-label-title="Competencia de Cursos"
        />
      </div>
    </div>
  </div>
</template>

<script>
import PieChart from '../components/chart/PieChart.vue';

export default {
  components: {
    PieChart
  },
  props: ['props'],
  data() {
    return {
      competencies: [],
      courses: [],
      labelPosition: 'right'
    };
  },
  created() {
    // Parsear los datos JSON recibidos como props
    const data = JSON.parse(this.props);
    this.competencies = data.competencies;
    this.courses = data.courses;
  },
  mounted() {
    this.updateLabelPosition();
    window.addEventListener('resize', this.updateLabelPosition);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.updateLabelPosition);
  },
  methods: {
    updateLabelPosition() {
      const containerWidth = this.$refs.chartContainer.clientWidth;
      this.labelPosition = containerWidth < 992 ? 'bottom' : 'right';
    }
  }
};
</script>

<lang-strings>
{
"block_dlreportgraph": [
"title_competencies",
"title_courses_competencies",
"accredited",
"not_accredited",
"failed",
"approved",
"noInformation"
]
}
</lang-strings>

<style lang="scss">
.tui-chartItem {
  h3.tui-titleGraph {
    font-family: 'Roboto-Bold', sans-serif;
    font-weight: 400;
    font-size: 20px;
    margin-bottom: 1.6em;
    line-height: 14.8px;
    letter-spacing: 0.37px;
    text-align: center;
  }
}

.tui-chartContainer::after {
  clear: both;
}

.tui-chartContainer {
  display: flex;
}

.tui-chartItem {
  width: 50%;
}

@media (max-width: 767.98px) {
  .tui-chartItem {
    margin-bottom: 2em;
  }
  .tui-chartContainer {
    flex-direction: column;
    align-items: center;
  }
}
</style>
