<template>
  <ChartJs
      :aria-label="ariaLabel"
      :data="chartData"
      :options="chartOptions"
      type="doughnut"
  />
</template>

<script>
import ChartJs from 'tui_charts/components/ChartJs';
import theme from 'tui/theme';
import { isRtl } from 'tui/i18n';

const screenDirectionRTL = isRtl();

// Función para obtener los valores de las variables CSS
function getCssVariable(varName) {
  return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
}

// Theme colours
let labelColor = theme.getVar('color-neutral-7');
let defaultColors = [
  getCssVariable('--dl-variante2'),
  getCssVariable('--dl-variante1'),
  getCssVariable('--dl-variante3'),
  theme.getVar('color-chart-background-2')
];


export default {
  components: {ChartJs},
  props: {
    // Lista de etiquetas a mostrar en el gráfico
    labels: {
      type: Array,
      required: true,
    },
    // Lista de valores numéricos que representan cada categoría
    dataValues: {
      type: Array,
      required: true,
    },
    // Number of achieved
    // Opcional: array de colores para cada porción del gráfico; si no se pasa, se usan los default.
    colors: {
      type: Array,
      default: () => defaultColors
    },
    // Título para el aria-label (accesibilidad)
    ariaLabelTitle: {
      type: String,
      default: 'Gráfico de pastel'
    },
    labelPosition: {
      type: String,
      default: 'right'
    }
  },
  mounted() {
    // Asegúrate de que Chart.js ya esté cargado
    if (window.Chart && window.Chart.elements && window.Chart.elements.Arc) {
      window.Chart.elements.Arc.prototype.draw = function () {
        const ctx = this._chart.ctx;
        const vm = this._view;

        const centerX = vm.x;
        const centerY = vm.y;
        const innerRadius = vm.innerRadius;
        const outerRadius = vm.outerRadius;
        const lineWidth = (outerRadius - innerRadius) * 1.3;
        const radius = innerRadius + lineWidth / 50;


        ctx.save();
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, vm.startAngle, vm.endAngle);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = vm.backgroundColor;
        // Con lineCap "round" se consigue el efecto borderRadius:50
        ctx.lineCap = 'round';
        ctx.stroke();
        ctx.restore();
      };
    } else {
      // Opcional: manejar el caso en que Chart.js aún no esté disponible.
      console.error('Chart.js aún no está cargado.');
    }
  },
  computed: {
    allValuesNull() {
      return this.dataValues.every(value => value == 0 || value == null);
    },
    chartOptions() {
      return {
        cutoutPercentage: 70,
        tooltips: {
          enabled: true,
          callbacks: {
            label: function (tooltipItem, data) {
              let dataset = data.datasets[tooltipItem.datasetIndex];
              let currentValue = dataset.dataraw[tooltipItem.index];
              let label = data.labels[tooltipItem.index];
              return label + ': ' + currentValue;
            }
          }
        },
        hover: {
          mode: 'nearest',
          intersect: true,
        },
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          animateScale: true,
          animateRotate: true
        },
        elements: {
          arc: {
            borderWidth: 0,
          },
        },
        legend: {
          position: this.labelPosition,
          labels: {
            fontColor: '#FFFFFF' // Cambia el color del texto de los labels a blanco
          },
          generateLabels: (chart) => {
            return chart.data.labels.map((label, index) => {
              return {
                text: label,
                fillStyle: chart.data.datasets[0].backgroundColor[index],
              };
            });
          },
          onClick() {
            return;
          },
        },
      };
    },
    chartData() {
      return {
        labels: this.labels,
        datasets: [
          {
            data: this.allValuesNull ? Array(this.dataValues.length - 1).fill(0).concat(1) : this.dataValues,
            dataraw: this.dataValues,
            backgroundColor: this.colors,
            borderWidth: 0,
            hoverBorderWidth: 3,
            hoverBorderColor: '#fff',
            hoverOffset: 5
          },
        ],
      };
    },
    ariaLabel() {
      var labelStr = this.labels
          .map(function (label, index) {
            return label + ': ' + this.dataValues[index];
          }.bind(this))
          .join(', ');
      return this.ariaLabelTitle + '. ' + labelStr;
    },
  }
};
</script>

<lang-strings>
{
"core_my": [
"a11y_overview_doughnut_label",
"a11y_overview_doughnut_label_completed",
"x_achieved",
"x_completed",
"x_not_progressed",
"x_not_started",
"x_progressed"
]
}
</lang-strings>

<style lang="scss">
:root {
  --dl-principal: #0C0A9A;
  --dl-principal2: #FFFFFF;
  --dl-secundario: #D52B1E;
  --dl-secundario2: #3A376F;
  --dl-background: #06032F;
  --dl-variante1: #FF6E6E;
  --dl-variante2: #6699FF;
  --dl-variante3: #6E7087;
  --dl-degradado: #6699FF;
}
</style>
