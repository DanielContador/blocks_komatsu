<template>
  <div
      :id="id"
      class="tui-dlGridItem1 overflow-hidden relative flex-col rounded-lg"
      :class="[{ 'zoom-effect': showInfo }, { 'card-stack': designitem === 'gridPrograms' }]"
      :designitem="designitem"
  >
    <div class="tui-dlTitleCardContainer">
      <div class="tui-dlBoxartContainer boxart-rounded boxart-size-16x9 relative">
        <img
            loading="lazy"
            :src="item.imageUrl"
            class="tui-dlBoxartImage boxart-image-in-padded-container w-full"
            :alt="item.fullname"
            :title="item.fullname"
        />
        <div class="sombra" :title="item.fullname"></div>
        <div class="progress-bar-overlay" v-if="item.progress != 100">
          <div class="progress-bar" :style="{ width: item.progress + '%' }">
          </div>
          <span class="progress-text">{{ item.progress }}%</span>
        </div>
        <div v-if="(item.top && item.top > 0) || item.isNew" class="tui-topLabel">
            <template v-if="item.top && item.top > 0">
              <span class="tui-topLabel__top">TOP {{item.top}}</span><br>
              <span class="tui-topLabel__moreview">{{$str('moreviews', 'theme_dlcourseflix')}}</span>
            </template>
            <template v-else-if="item.isNew">
              <span class="tui-topLabel__new">{{$str('new', 'moodle')}}</span><br>
            </template>
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
        <span>
          {{ item.category }}
        </span>
      </div>
      <div class="flex gap-10 justify-between items-end mt-0 w-full">
        <CourseButton :link="item.link" :designitem="designitem"/>
        <span v-if="item.duration" class="tui-dlCardCourseDuration text-base leading-relaxed text-white" aria-label="Course duration">
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
        return ['id', 'category', 'shortname', 'fullname', 'link', 'imageUrl','duration']
            .every(prop => obj.hasOwnProperty(prop));
      }
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
    designitem: {
      type: String,
      required: true
    },
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      infoContainerStyle: {},
      textbutton: this.designitem
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
    }
  }
};
</script>

<lang-strings>
  {
      "theme_dlcourseflix": [
          "moreviews"
      ],
      "moodle": [
          "new"
      ]	
  }
</lang-strings>

<style lang="scss">
.tui-dlGridItem1 {
  box-shadow: 0px 8px 40px rgba(0, 0, 0, 0.5);
  transition: transform 0.3s ease-in-out;
  transform-origin: top;
  cursor: pointer;
  &.zoom-effect {
    transform: scale(1.3);
    z-index: 10; /* Ensure this value is higher than other elements */
    overflow: visible;
    z-index: 1000;
    .tui-dlCourseButton {
      transform: scale(0.714);
      transform-origin: bottom left;
    }
    .tui-dlCardCourseDuration {
      transform: scale(0.714);
      transform-origin: bottom right;
      text-align: end;
    }
    &.card1, &.card2 {
      display: none;
    }
  }
  &.card-stack {
    &:nth-child(1) {
     /* transform: translate(0, 0);*/
      z-index: 3;
    }
    &:nth-child(2) {
      transform: translate(5px, -1px);
      z-index: 2;
    }
    &:nth-child(3) {
      transform: translate(12px, 0px);
      z-index: 1;
    }
  }
  &.card-stack.zoom-effect {
    z-index: 4;
  }
  .tui-dlCourseInfoContainer {
    color: #fff;
    display: flex;
    padding: 18px;
    flex-direction: column;
    /*align-items: flex-start;*/
    background-color: #06032f;
    border-radius: 0 0 5px 5px;
    .tui-dlCardCourseTitle {
      margin-bottom: 16px;
      font-size: 15px; // Adjusted for scaling
      line-height: 18px;
      font-family: 'Roboto-Bold', sans-serif;

    }
    .tui-dlCardCourseCategories {
      color: #f99;
      font-size: 12px; // Adjusted for scaling
      line-height: 18px;
      font-family: 'Roboto-Regular', sans-serif;
    }
  }
}
.first-item {
  .tui-dlGridItem1 {
    transform-origin: top left !important;
  }
}
.last-item {
  .tui-dlGridItem1 {
    transform-origin: top right !important;
  }
}
.tui-dlBoxartImage {
  width: 281.37px;
  height: 158px;
}
div.tui-dlGridItem1[designitem="gridInProgress"] .progress-bar-overlay {
  display: block;
  position: absolute;
  bottom: 15px;
  left: 5px;
  right: 5px;
  border: 1px solid white;
  overflow: hidden;
  height: 20px;
}
div.tui-dlGridItem1[designitem="gridInProgress"] .progress-bar-overlay .progress-bar {
  height: 100%;
  background-color: white;
  transition: width 0.3s ease;
  position: absolute;
}
div.tui-dlGridItem1[designitem="gridInProgress"] .progress-bar-overlay .progress-text {
  color: #f37021;
  font-weight: bold;
  z-index: 1;
  text-align: center;
  position: relative;
  margin: auto;
  display: flow;
}
div.tui-dlGridItem1[designitem="gridInProgress"] .sombra {
  /* background: red; */
  width: 100%;
  height: 100%;
  position: absolute;
  background: linear-gradient(to top, var(--dl-principal) 0%, transparent 100%);
}
/*div[designitem="gridPrograms"] .tui-dlBoxartImage {
  width: 100%;
  height: 158px;
  display: block;
}*/
div[designitem="gridPrograms"] .card {
  box-shadow: 6px 5px 7px 0 #00000070;
  border-radius: 7px;
  height: 330px;
}
div[designitem="gridPrograms"] .card1{
  height: 158px !important;
  position: absolute;
  /*bottom: -25px;*/
  z-index: -2;
  width: 100%;
  border-top-left-radius: 3px !important;
  border-top-right-radius: 3px;
  background: #656388;
  left: 0px;
  @media screen and (max-width: 767.98px) {
    height: 115px !important;
  }
}
div[designitem="gridPrograms"] .card1 img, div[designitem="gridPrograms"] .card2 img{
  display: none;
}
div[designitem="gridPrograms"] .card2{
  height: 158px !important;
  position: absolute;
  /*bottom: -50px;*/
  z-index: -2;
  width: 100%;
  border-top-left-radius: 3px !important;
  border-top-right-radius: 3px;
  background: #36335B;
  left: 0;
  @media screen and (max-width: 767.98px) {
    height: 115px !important;
  }
}
div[designitem="gridInProgress"] .card1, div[designitem="gridInProgress"] .card2,
div[designitem="gridCourses"] .card1, div[designitem="gridCourses"] .card2{
  display: none;
}
.progress-bar-overlay {
  display: none;
}
div.tui-dlGridItem1.zoom-effect[designitem="gridInProgress"].progress-bar-overlay {
  display: none !important;
}
div.courses-items[designitem="gridPrograms"]{
  margin-right: 30px;
  margin-bottom: 30px;
}
.tui-dlGridItem1.card-stack:nth-child(1) {
  box-shadow: 6px 5px 7px 0 #00000070;
}
.tui-topLabel {
  position: absolute;
  top: 0;
  left: 0;
  background-color: var(--dl-secundario);
  color: #fff;
  padding: 7px 8px;
  border-radius: 5px 0 5px 0;
  text-align: center;
  line-height: 1rem;
  font-weight: bold;
  min-height: 36px;
  align-content: center;
  &__top {
    font-size: 12px;
  }
  &__moreview {
    font-size: 10px;
  }
  &__new {
    font-size: 12px;
    text-transform: uppercase;
  }
}
</style>
