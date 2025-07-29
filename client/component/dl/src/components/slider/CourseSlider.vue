<template>
  <div class="tui-dlCourseCarousel slide" data-ride="carousel">
    <ol class="tui-dlCarouselIndicators">
      <li
          v-for="(course, index) in sliders"
          :key="index"
          :data-target="'.tui-dlCourseCarousel'"
          :data-slide-to="index"
          :class="{ active: currentSlide === index }"
          @click="setSlide(index)"
      ></li>
    </ol>
    <div class="tui-dlCarouselInner">
      <div
          class="tui-dlCarouselItem"
          v-for="(course, index) in sliders"
          :key="index"
          :class="{ active: currentSlide === index }"
      >
        <a>
          <div class="tui-dlImageContainer">
            <img
                loading="lazy"
                class="d-block w-100"
                :src="course.imageUrl"
            :alt="course.fullname"
            />
            <div class="tui-dlGradientOverlay"></div>
          </div>
        </a>
        <div class="tui-dlCourseInfo">
          <header class="tui-dlCourseHeader">
            <span>Recomendados para ti</span>
          </header>
          <h1 class="tui-dlCourseTitle">{{ course.fullname }}</h1>
          <div class="tui-dlCourseCategories">
            <span>
              {{ course.category }}
            </span>
          </div>
          <CourseButton :link="course.link" :designitem="designitem"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import CourseButton from '../button/CourseButton.vue';
import MoreViewQuery from 'local_dlservices/graphql/moreviewsitems'; // Importa la consulta

export default {
  components: {
    CourseButton
  },
  props: {
    limit: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      sliders: [],
      currentSlide: 0,
      previousSlide: 0,
      slideInterval: null,
      designitem: ''
    };
  },
  apollo: {
    sliders: {
      query: MoreViewQuery,
      variables() {
        return {
          limit: this.limit,
          recommended: true, // Ajusta para obtener solo los recomendados
          designitem: ''
        };
      },
      update: data => data.local_dlservices_moreviewsitems.items || [], // Ajusta para acceder a los items
    },
  },
  methods: {
    nextSlide() {
      this.previousSlide = this.currentSlide;
      this.currentSlide = (this.currentSlide + 1) % this.sliders.length;
      this.updateSlidePosition(this.currentSlide, this.previousSlide);
    },
    prevSlide() {
      this.previousSlide = this.currentSlide;
      this.currentSlide = (this.currentSlide - 1 + this.sliders.length) % this.sliders.length;
      this.updateSlidePosition(this.currentSlide, this.previousSlide);
    },
    setSlide(index) {
      this.previousSlide = this.currentSlide;
      this.updateSlidePosition(index, this.previousSlide);
    },
    updateSlidePosition(currentIndex, previousIndex) {
      const slides = this.$el.querySelectorAll('.tui-dlCarouselItem');

      if (slides.length === 0) {
        console.warn('No hay slides disponibles.');
        return; // Salir si no hay slides
      }
      if (slides[previousIndex]) {
        slides[previousIndex].classList.add('tui-dlCarouselItemLeft');
        setTimeout(() => {
          slides[previousIndex].classList.remove('tui-dlCarouselItemLeft');
          this.currentSlide = currentIndex;
        }, 400); // Match the transition duration
      }
    },
    startSlideShow() {
      this.slideInterval = setInterval(this.nextSlide, 5000);
    },
    stopSlideShow() {
      clearInterval(this.slideInterval);
    }
  },
  mounted() {
    this.updateSlidePosition(this.currentSlide, this.previousSlide);
    this.startSlideShow();
  },
  beforeDestroy() {
    this.stopSlideShow();
  }
};
</script>

<style lang="scss">
.tui-dlCourseCarousel {
  position: relative;
  .tui-dlCarouselInner {
    position: relative;
    width: 100%;
    padding-bottom: 40%;
    overflow: hidden;
    display: flex;
    transition: transform 0.6s ease-in-out;
    @media screen and (max-width: 767.98px) {
      padding-bottom: 50%;
    }
  }
  .tui-dlCarouselItem {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.6s ease-in-out, opacity 0.6s ease-in-out;

    &.active {
      opacity: 1;
    }

    &:not(.active) {
      opacity: 0;
      display: none;
    }

    &.tui-dlCarouselItemLeft {
      transform: translateX(-100%);
    }
  }
  .tui-dlImageContainer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
  }
  .tui-dlGradientOverlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 20%, #06032F 90%);
  }
  .tui-dlCourseInfo {
    position: absolute;
    bottom: 15%;
    left: 5%;
    color: white;
    max-width: 575px;
    @media screen and (max-width: 767.98px) {
      max-width: 300px; // Adjusted for scaling
    }
    font-weight: bold;
    // box-shadow: 0px 0px 13px rgba(0, 0, 0, 0.2);
  }
  .tui-dlCourseHeader {
    background-color: var(--dl-secundario);
    padding: 0 4px;
    letter-spacing: 3.5px;
    font-family: 'Roboto-Light', sans-serif;
    line-height: 23px;
    width: 314px;
    height: 23px;
    margin-bottom: .6em;
    justify-content: center;
    text-align: center;
    vertical-align: middle;
    align-content: center;
    @media screen and (min-width: 768px) {
      font-size: 19px; // Adjusted for scaling
    }
    @media screen and (max-width: 767.98px) {
      font-size: 1rem; // Adjusted for smaller screens
      width: fit-content;
    }
  }
  .tui-dlCourseTitle {
    margin-top: 2px;
    font-size: 2rem; // Adjusted for scaling
    line-height: 58px;
    font-family: 'Roboto-Bold', sans-serif;
    @media screen and (min-width: 768px) {
      font-size: 3.188rem; // Adjusted for scaling
    }
    @media screen and (max-width: 767.98px) {
      font-size: 1.5rem; // Adjusted for smaller screens
      line-height: 1;
    }
  }
  .tui-dlCourseCategories {
    display: flex;
    gap: 2px;
    margin: .8em 0 2rem 0;
    font-size: 16px;
    font-family: 'Roboto-Regular', sans-serif;
    white-space: nowrap;
    line-height: 24px;
    @media screen and (max-width: 767.98px) {
      font-size: 1rem; // Adjusted for smaller screens
      line-height: 1;
      margin: .8em 0 1em 0;
    }
  }
  .tui-dlCategorySeparator {
    color: #595959;
    margin: 0 .8em;
  }

  .tui-dlCarouselControlPrev,
  .tui-dlCarouselControlNext {
    background: rgba(0, 0, 0, 0.5);
  }

  @media screen and (min-width: 768px) {
    .tui-dlCarouselIndicators {
      bottom: 3em !important;
    }
  }
  .tui-dlCarouselIndicators {
    position: absolute;
    bottom: 10px;
    left: 50%;
    z-index: 15;
    width: 60%;
    padding-left: 0;
    margin-left: -30%;
    text-align: center;
    list-style: none;
    display:flex;
    justify-content: center;
    align-items: center;
  }
  .tui-dlCarouselIndicators li {
    display: inline-block;
    width: 10px !important;
    height: 10px !important;
    position: relative;
    opacity: 76%;
    margin-right: 1px;
    text-indent: -999px;
    cursor: pointer;
    background-color: #fff !important;
    margin-right: 8px !important;
    border-radius: 10px;
  }
  .tui-dlCarouselIndicators li.active {
    width: 14px !important;
    height: 14px !important;
    background-color: #ccc !important;
  }
}
</style>
