<template>
  <div class="tui-gridRow-container">
    <!-- El resto del template permanece igual hasta los métodos de scroll -->
    <section role="region" class="mb-12 tui-gridRow-section">
      <ComponentLoading v-if="loadingGroupedCourses"/>
      <div v-else v-for="(categoryGroup, categoryName) in groupedCourses" :key="categoryName">
        <div
            class="tui-dlGridRow">
          <span
              v-if="categoryGroup.length > 5"
              class="tui-dlHandle tui-dlHandlePrev"
              tabindex="0"
              role="button"
              aria-label="Ver títulos anteriores"
              @click="scrollLeft(categoryName)"
          >
            <i class="fas fa-chevron-left"></i>
          </span>
          <div class="tui-dlGridRowContent" :ref="'gridRowContent-' + categoryName">
            <div
                v-for="(course, index) in categoryGroup"
                :key="categoryName + course.id"
                @mouseover="startHover(index, categoryName, $event)"
                @mouseleave="stopHover"
                :class="[
                  'tui-dlGridItemContainer courses-items',
                  `index-${index}`,
                  { 'first-item': firstItemIndex === index && hoveredCategory === categoryName },
                  { 'last-item': lastItemIndex === index && hoveredCategory === categoryName }
                ]"
                :designitem="design"
            >
              <GridCard
                  v-if="validateCourse(course)"
                  :item="course"
                  :id="'course-'+course.id"
                  :index="index"
                  :showInfo="hoveredIndex === index && hoveredCategory === categoryName"
                  :isFirst="firstItemIndex === index"
                  :isLast="lastItemIndex === index"
                  :designitem="design"
              />
              <GridCard
                  v-if="validateCourse(course)"
                  :item="course"
                  :id="'course-1'+course.id"
                  :index="index"
                  :showInfo="hoveredIndex === index && hoveredCategory === categoryName"
                  :isFirst="firstItemIndex === index"
                  :isLast="lastItemIndex === index"
                  :designitem="design"
                  :class="['card card1']"
              />
              <GridCard
                  v-if="validateCourse(course)"
                  :item="course"
                  :id="'course-2'+course.id"
                  :index="index"
                  :showInfo="hoveredIndex === index && hoveredCategory === categoryName"
                  :isFirst="firstItemIndex === index"
                  :isLast="lastItemIndex === index"
                  :designitem="design"
                  :class="['card card2']"
              />
            </div>
          </div>
          <span
              v-if="categoryGroup.length > 5"
              class="tui-dlHandle tui-dlHandleNext"
              tabindex="0"
              role="button"
              aria-label="Ver títulos siguientes"
              @click="scrollRight(categoryName)"
          >
            <i class="fas fa-chevron-right"></i>
          </span>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import GridCard from '../grid/CourseCard.vue';
import MyCurrentCoursesQuery from 'local_dlservices/graphql/mycurrent_coursesitems';
import MyCoursesQuery from 'local_dlservices/graphql/mycoursesitems';
import MyProgramsQuery from 'local_dlservices/graphql/myprograms';
import NoProgressQuery from 'local_dlservices/graphql/noprogress_items';
import CompletedQuery from 'local_dlservices/graphql/completed_items';
import ComponentLoading from 'tui/components/loading/ComponentLoading';

export default {
  props: ['design', 'programscourses', 'completedprograms'],
  components: {
    GridCard,
    ComponentLoading
  },
  data() {
    return {
      timer: null,
      hoveredIndex: 1,
      hoveredCategory: null,
      firstItemIndex: null,
      lastItemIndex: null,
      courses: [],
      groupedCourses: null,
      title: '',
      // design:''
    };
  },
  apollo: {
    courses: {
      query() {
        switch (this.design) {
          case 'gridPrograms':
            return MyProgramsQuery;
          case 'gridCourses':
            return MyCoursesQuery;
          case 'gridNotProgress':
            return NoProgressQuery;
          case 'gridCompleted':
            return CompletedQuery
          default:
            return MyCurrentCoursesQuery;
        }
      },
      variables() {
        switch (this.design) {
          case 'gridPrograms':
            return {
              spage: 0,
              completedprograms: this.completedprograms
            };
          case 'gridCourses':
            return {
              spage: 0,
              programscourses: this.programscourses
            };
          default:
            return {
              spage: 0
            };
        }
      },
      update(data) {
        let items = [];
        if (this.design === 'gridPrograms') {
          items = data.local_dlservices_myprograms.programs || [];
        } else if (this.design === 'gridCourses') {
          items = data.local_dlservices_mycoursesitems.items || [];
        } else if (this.design === 'gridNotProgress') {
          items = data.local_dlservices_noprogress_items.items || [];
        } else if (this.design === 'gridCompleted') {
          items = data.local_dlservices_completed_items.items || [];
        } else {
          items = data.local_dlservices_mycurrent_coursesitems.items || [];
        }
        this.groupedCourses = items.reduce((acc, course) => {
          const categoryName = 'moreviews';
          if (!acc[categoryName]) {
            acc[categoryName] = [];
          }
          acc[categoryName].push(course);
          return acc;
        }, {});
        return items;
      }
    }
  },
  computed: {
    loadingGroupedCourses() {
      return this.groupedCourses == null;
    }
  },
  watch: {
    loadingGroupedCourses(newVal) {
      if (!newVal && Object.keys(this.groupedCourses).length > 0) {
        this.$emit('loaded', 'Courses Loaded'); // Emit event when loading is complete
      }
    }
  },
  methods: {
    validateCourse(course) {
      const requiredProps = ['id', 'category', 'shortname', 'fullname', 'link', 'imageUrl', 'duration'];
      const missingProps = requiredProps.filter(prop => !course.hasOwnProperty(prop) || course[prop] === undefined);
      return missingProps.length <= 0;
    },
    startHover(index, categoryName, event) {
      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        this.hoveredIndex = index;
        this.hoveredCategory = categoryName;
        const item = event.target;
        const container = this.$refs[`gridRowContent-${categoryName}`];
        if (container && container[0]) {
          const containerRect = container[0].getBoundingClientRect();
          const itemRect = item.getBoundingClientRect();
          if (itemRect.left - itemRect.width * 0.2 <= containerRect.left) {
            this.firstItemIndex = index;
          } else if (itemRect.right + itemRect.width * 0.2 >= containerRect.right) {
            this.lastItemIndex = index;
          } else {
            this.firstItemIndex = null;
            this.lastItemIndex = null;
          }
        }
      }, 500);
    },

    stopHover() {
      clearTimeout(this.timer);
      this.hoveredIndex = null;
      this.hoveredCategory = null;
    },

    scrollLeft(categoryName) {
      const container = this.$refs[`gridRowContent-${categoryName}`];
      if (container[0]) {
        const scrollAmount = container[0].clientWidth * 0.8;
        const startPosition = container[0].scrollLeft;
        const targetPosition = Math.max(0, startPosition - scrollAmount);
        this.smoothScroll(container[0], startPosition, targetPosition);
      }
    },

    scrollRight(categoryName) {
      const container = this.$refs[`gridRowContent-${categoryName}`];
      if (container[0]) {
        const scrollAmount = container[0].clientWidth * 0.8;
        const startPosition = container[0].scrollLeft;
        const maxScroll = container[0].scrollWidth - container[0].clientWidth; // Máximo desplazamiento posible
        const targetPosition = Math.min(maxScroll, startPosition + scrollAmount); // Asegurarse de no desplazarse más allá del máximo
        this.smoothScroll(container[0], startPosition, targetPosition);
      }
    },

    smoothScroll(container, startPosition, targetPosition) {
      const duration = 500;
      const startTime = performance.now();

      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Función de easing
        const easeProgress = 1 - Math.pow(1 - progress, 3);

        container.scrollLeft = startPosition + (targetPosition - startPosition) * easeProgress;

        if (progress < 1) {
          requestAnimationFrame(animate);
        }
      };

      requestAnimationFrame(animate);
    },

    isFirstItem(index) {
      return index === this.firstItemIndex;
    },

    isLastItem(index) {
      return index === this.lastItemIndex;
    }
  }
};
</script>

<style lang="scss">
.tui-componentLoading .tui-iconLoading {
  font-size: 2.6rem;
}

.tui-dlCourseCategoryHeading {
  font-size: 20px;
  font-family: 'Roboto-Bold', sans-serif;
  line-height: 32px;
}

.tui-dlCourseCategoryHeading.categories {
  margin-top: 65px;
}

.tui-dlGridRow {
  display: flex;
  position: relative;
  width: 100%;

  &.tui-dlItemHovered {
    margin-bottom: -400px;

    .tui-dlGridRowContent {
      padding-bottom: 400px;
    }

    .tui-dlHandle {
      height: calc(100% - 400px);
      display: none !important;
    }
  }

  &:hover {
    .tui-dlHandle {
      display: flex;
    }
  }

  .tui-dlHandle {
    position: absolute;
    top: 0;
    z-index: 100;
    cursor: pointer;
    font-size: 2.3em;
    padding: 0 0.5em;
    bottom: 0;
    display: none;

    i {
      align-content: center;
      color: #FFF;
    }

    &.tui-dlHandlePrev {
      left: calc(-1 * var(--dl-grid-gap));
      background: linear-gradient(to left, rgba(0, 0, 0, 0) 0%, #06032F 100%);
    }

    &.tui-dlHandleNext {
      right: calc(-1 * var(--dl-grid-gap));
      background: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, #06032F 100%);
    }
  }
}

.tui-dlGridRowContent {
  overflow: hidden;
  white-space: nowrap;
  margin-left: calc(-1 * var(--dl-grid-gap)); /* Extend into the left margin */
  margin-right: calc(-1 * var(--dl-grid-gap)); /* Extend into the right margin */
  padding-left: var(--dl-grid-gap); /* Adjust padding to compensate for negative margin */
  padding-right: var(--dl-grid-gap); /* Adjust padding to compensate for negative margin */
}

.tui-dlGridItemContainer {
  position: relative;
  display: inline-block;
  width: 281.37px;
  height: 158px;
  white-space: normal;
  transition: transform 0.3s ease-in-out, z-index 0.3s;
}

.courses-items {
  padding: 0 16px 0 0;
}

.more-views-courses {
  padding: 0;
}

.tui-dlSliderItem {
  width: 18%;
}

@media (max-width: 767.98px) {
  .tui-gridRow-section {
    margin-left: 0;
    margin-right: 0;
  }
  .tui-dlGridRowContent {
    margin-left: 0;
    margin-right: 0;
    padding-left: 0;
    padding-right: 0;
  }
  .tui-dlGridItemContainer.courses-items {
    width: 205px;
    height: 115px;
  }
  .tui-dlHandle {
    display: none !important;
  }
  .tui-dlBoxartImage {
    height: 115px;
  }
  .tui-dlGridRowContent {
    overflow-x: scroll;
    width: 100%;
  }
  /*.tui-dlCourseCategoryHeading {
    font-size: 14px;
    line-height: 20px;
  }*/
  .tui-dlGridItemContainer.more-views-courses {
    width: 200px;
    height: 116px;
  }
}
</style>
