
<template>
  <div
      :id="'course-' + item.id"
      class="tui-dlGridItem2 overflow-hidden relative table rounded-lg"
      :class="[{ 'zoom-effect': showInfo }]"
  >
    <div class="tui-dlTitleCardContainer">
      <div class="tui-dlBoxartContainer boxart-rounded boxart-size-16x9">
        <div class="content-wrapper">
          <span class="styled-number">
            {{ index+1 }}
          </span>
          <img
            loading="lazy"
            :src="item.imageUrl"
            class="tui-dlBoxartImage boxart-image-in-padded-container w-full"
            :alt="item.fullname"
            :title="item.fullname"
          />
        </div>

      </div>
    </div>
    <div
        class="tui-dlCourseInfoContainer"
        v-if="showInfo"
        :style="infoContainerStyle"
    >
      <h2 class="tui-dlCardCourseTitle">
        {{ item.fullname }}
      </h2>
      <div class="tui-dlCardCourseCategories">
        <span>{{ item.category }}</span>
      </div>
      <div class="flex gap-10 justify-between items-end mt-0 w-full">
        <CourseButton :link="item.link" designitem = "gridCourses" />
        <span class="tui-dlCardCourseDuration text-base leading-relaxed text-white" aria-label="Course duration">
                    Duración: {{ item.duration }}
                </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import CourseButton from '../button/CourseButton.vue';
import '../../styles/global.scss';

export default {
  components: {
    CourseButton
  },
  props: {
    item: {
      type: Object,
      required: true,
      validator: function(obj) {
        const requiredProps = ['id', 'category', 'shortname', 'fullname', 'link', 'imageUrl', 'duration'];
        const missingProps = requiredProps.filter(prop => !obj.hasOwnProperty(prop) || obj[prop] === undefined);

        if (missingProps.length > 0) {
          console.warn(`Missing properties in item: ${missingProps.join(', ')}`);
          return false;
        }
        return true;
      }
    },
    index: {
        type: Number,
        required: true
    },
    showInfo: {
      type: Boolean,
      required: true
    },
    isFirst: {
      type: Boolean,
      required: true
    },
    isLast: {
      type: Boolean,
      required: true
    },
  },
  data() {
    return {
      infoContainerStyle: {}
    };
  },
  watch: {
    showInfo(newVal) {
      if (newVal) {
        this.setInfoContainerStyle();
      }
    }
  },
  mounted() {
    if (this.showInfo) {
      this.setInfoContainerStyle();
    }
  },
  methods: {
    setInfoContainerStyle() {
      this.$nextTick(() => {
        this.infoContainerStyle = {
          position: 'absolute',
          top: '100%',
          left: '0',
          width: '100%',
          zIndex: 10
        };
      });
    },
    validateCourse(course) {
      const requiredProps = ['id', 'category', 'shortname', 'fullname', 'link', 'imageUrl', 'duration'];
      const missingProps = requiredProps.filter(prop => !course.hasOwnProperty(prop) || course[prop] === undefined);

      if (missingProps.length > 0) {
        console.error(`Course is missing properties: ${missingProps.join(', ')}`);
        return false;
      }
      return true;
    }
  }
};
</script>

<style lang="scss">
.tui-dlGridItem2 {
  box-shadow: none;
  transition: transform 0.3s ease-in-out;
  transform-origin: top;
  cursor: pointer;
  &.zoom-effect {
    transform: scale(1.3);
    z-index: 10; /* Ensure this value is higher than other elements */
    overflow: visible;
    .tui-dlCourseButton {
      transform: scale(0.714);
      transform-origin: bottom left;
    }
    .tui-dlCardCourseDuration {
      transform: scale(0.714);
      transform-origin: bottom right;
      text-align: end;
    }
    .content-wrapper {
      .tui-dlBoxartImage {
         width: 100%;
         max-width: unset !important;
      }
      .styled-number {
         display: none;
      }
    }
  }
  .tui-dlCourseInfoContainer {
    color: #FFF;
    display: flex;
    padding: 18px;
    flex-direction: column;
    /*align-items: flex-start;*/
    background-color: #06032F;
    border-radius: 0 0 5px 5px;
    .tui-dlCardCourseTitle {
      margin-bottom: 16px;
      font-size: 15px; // Adjusted for scaling
      line-height: 18px;
      font-family: 'Roboto-Bold', sans-serif;
    }
    .tui-dlCardCourseCategories {
      color: #F99;
      font-size: 12px; // Adjusted for scaling
      line-height: 18px;
      font-family: 'Roboto-Regular', sans-serif;
    }
  }
}
.first-item {
  .tui-dlGridItem2 {
    transform-origin: top left !important;
  }
}
.last-item {
  .tui-dlGridItem2 {
    transform-origin: top right !important;
  }
}
// .tui-dlBoxartImage {
//   width: 281.37px;
//   height: 158px;
// }

/*Cambios Container*/
.tui-dlBoxartContainer {
  display: flex; /* Habilita Flexbox */
  align-items: center; /* Centra verticalmente los elementos */
  justify-content: center; /* Centra horizontalmente los elementos */
  height: 100%; /* Asegura que el contenedor ocupe todo el espacio disponible */
}

.content-wrapper {
  /*display: flex;  Habilita Flexbox dentro del contenedor */
  align-items: center; /* Alinea verticalmente los elementos */
  width: 100%; /* Asegura que el contenedor ocupe todo el espacio disponible */
  position: relative;
  display: inline-flex; /* Asegura que el contenedor se ajuste al contenido */
  text-align: right; /* Centra el texto horizontalmente */
  overflow: hidden;
}


.content-wrapper>img {
  flex: 1; /* Ambos elementos ocupan el mismo espacio */
  max-width: 60%; /* Limita el ancho m�ximo al 50% del contenedor */
  box-sizing: border-box; /* Incluye el padding y border en el c�lculo del ancho */
  z-index: 1;
}

.content-wrapper span.styled-number {
  font-family: Open Sans;
  font-size: 250px;
  line-height: 0.8;
  letter-spacing: -20px;
  color: transparent;
  -webkit-text-stroke: 3px #2B2B2B;
  font-weight: 900;
  margin-right: -2.4rem;
  margin-top: -1rem;
  font-variant-numeric: proportional-nums;
  @media screen and (max-width: 767.98px) {
    font-size: 150px;
    margin-right: 0;
  }
}

/*span.number-0 {
  margin-left: -5px;
}

span.number-1, span.number-0 {
  !*font-size: 113px;
  transform: scale(1.1, 1.8);*!
  float: left;
}*/

span.styled-number img {
  height: 152px;
  margin-top: -12px;
}
.tui-dlGridItemContainer.more-views-courses.index-9{
  width: auto;
}

.tui-dlGridItemContainer.more-views-courses img.tui-dlBoxartImage {
  height: 185px;
  width: 162px;
  border-radius: 4px;
  @media screen and (max-width: 767.98px) {
    height: 116px;
    width: 100px;
  }
}

.content-wrapper>img {
  object-fit: cover;
  height: 158px;
  display: block;
  transition: transform 2s ease-in-out;
}

.tui-dlBoxartImage {
  width: 100%;
  height: 158px;
  display: block;
  border-radius: 5px 5px 0 0;
  object-fit: cover;
}

.content-wrapper:hover .styled-number {
  // opacity: 0;
}

// .content-wrapper:hover img {
//   transform: scale(2.1);
// }
</style>
