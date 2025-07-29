<template>
  <div class="tui-gridRow-container">
    <!-- El resto del template permanece igual hasta los m�todos de scroll -->
    <section role="region" class="mb-12 tui-dlGridRowSection">
      <ComponentLoading v-if="loadingCoursesMoreViews" />
      <div v-else v-for="(categoryGroup, categoryName) in groupedCoursesMoreViews" :key="categoryName">
        <p class="tui-dlCourseCategoryHeading">
          Los más vistos <i class="ml-4 fa-light fa-chevron-right"></i>
        </p>
        <div :class="['tui-dlGridRow', { 'tui-dlItemHovered': hoveredIndex !== null && hoveredCategory === categoryName }]">
          <span
              v-if="categoryName.length > 5"
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
                  'tui-dlGridItemContainer more-views-courses',
                  `index-${index}`,
                  { 'first-item': firstItemIndex === index && hoveredCategory === categoryName },
                  { 'last-item': lastItemIndex === index && hoveredCategory === categoryName }
                ]"
            >
              <GridMoreViewItem
                  v-if="validateCourse(course)"
                  :item="course"
                  :index="index"
                  :showInfo="hoveredIndex === index && hoveredCategory === categoryName"
                  :isFirst="firstItemIndex === index"
                  :isLast="lastItemIndex === index"
              />
            </div>
          </div>
          <span
              v-if="categoryName.length > 5"
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

    <section role="region" class="mb-12 tui-dlGridRowSection">
      <ComponentLoading v-if="loadingGroupedCourses" />
      <div v-else v-for="(categoryGroup, categoryName) in groupedCourses" :key="categoryName">
        <p class="tui-dlCourseCategoryHeading categories">
          {{ categoryName }} <i class="ml-4 fa-light fa-chevron-right"></i>
        </p>
        <div :class="['tui-dlGridRow', { 'tui-dlItemHovered': hoveredIndex !== null && hoveredCategory === categoryName }]">
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
                :key="course.id"
                @mouseover="startHover(index, categoryName, $event)"
                @mouseleave="stopHover"
                :class="[
                  'tui-dlGridItemContainer courses-items',
                  `index-${index}`,
                  { 'first-item': firstItemIndex === index && hoveredCategory === categoryName },
                  { 'last-item': lastItemIndex === index && hoveredCategory === categoryName }
                ]"
            >

              <GridItem1
                  :item="course"
                  :id="'course-'+course.id"
                  :showInfo="hoveredIndex === index && hoveredCategory === categoryName"
                  :isFirst="firstItemIndex === index"
                  :isLast="lastItemIndex === index"
                  :designitem="design"
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
import GridItem1 from './GridItem1.vue';
import GridMoreViewItem from './GridMoreViewItem.vue';
import CoursesMoreViewQuery from 'local_dlservices/graphql/moreviewsitems';
import AllCoursesByCategoryQuery from 'local_dlservices/graphql/items';
import ComponentLoading from 'tui/components/loading/ComponentLoading';

export default {
  components: {
    GridItem1,
    GridMoreViewItem,
    ComponentLoading
  },
  data() {
    return {
      timer: null,
      hoveredIndex: 1,
      hoveredCategory: null,
      firstItemIndex: null,
      lastItemIndex: null,
      coursesmoreviews:[],
      courses: [],
      groupedCourses: null,
      groupedCoursesMoreViews: null,
      // designitem:'',
      design:'',
      searchTerm: '',
      loadingGroupedCourses: false,
      loadingCoursesMoreViews: false
    };
  },
  created() {
    document.getElementById('genericsearchbox')?.addEventListener('keyup', this.handleSearchEnter);
    this.fetchMoreViewCourses();
    this.fetchGroupedCourses();
  },
  methods: {
    fetchGroupedCourses() {
      if (this.loadingGroupedCourses) return;
      this.loadingGroupedCourses = true;
      this.$apollo.query({
          query: AllCoursesByCategoryQuery,
          variables: { searchTerm: this.searchTerm, spage: 0 }
      })
      .then(response => {
        const items = response.data.local_dlservices_items.items || [];
        this.groupedCourses = items.reduce((acc, course) => {
          const categoryName = course.category || 'Sin categoría';
          if (!acc[categoryName]) {
            acc[categoryName] = [];
          }
          acc[categoryName].push(course);
          return acc;
        }, {});
        this.courses = items;
        this.loadingGroupedCourses = false;
      })
      .catch(error => {
        console.error('Error fetching courses:', error);
        this.loadingGroupedCourses = false;
      });
    },
    fetchMoreViewCourses() {
      if (this.loadingCoursesMoreViews) return;
      this.loadingCoursesMoreViews = true;
      this.$apollo.query({
        query: CoursesMoreViewQuery,
        variables: { limit: 10,
                    recommended: false,
                    design: '' }
      })
      .then(response => {
        const items = response.data.local_dlservices_moreviewsitems.items || [];
        this.groupedCoursesMoreViews = items.reduce((acc, course) => {
          const categoryName = 'moreviews';
          if (!acc[categoryName]) {
            acc[categoryName] = [];
          }
          acc[categoryName].push(course);
          return acc;
        }, {});
        this.coursesmoreviews = items;
        this.loadingCoursesMoreViews = false;
      })
      .catch(error => {
        console.error('Error fetching courses:', error);
        this.loadingCoursesMoreViews = false;
      });
    },
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
          const maxScroll = container[0].scrollWidth - container[0].clientWidth; // M�ximo desplazamiento posible
          const targetPosition = Math.min(maxScroll, startPosition + scrollAmount); // Asegurarse de no desplazarse m�s all� del m�ximo
          this.smoothScroll(container[0], startPosition, targetPosition);
        }
    },

    smoothScroll(container, startPosition, targetPosition) {
      const duration = 500;
      const startTime = performance.now();

      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Funci�n de easing
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
    },

    handleSearchEnter(event) {
      if (event.key === 'Enter') {
          const searchTerm = event.target.value.trim();
          this.searchTerm = searchTerm;
          this.fetchGroupedCourses();
      }
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
  line-height: 34px;
}

.tui-dlCourseCategoryHeading.categories{
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
    .tui-dlGridRowContent.no-scroll {
      overflow: hidden; /* Oculta los scrollbars */
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
.tui-dlGridItemContainer.more-views-courses {
  height: 185px;
}

.more-views-courses {
  padding: 0;
}

.courses-items {
  /*padding: 0 .2vw;*/
  padding: 0 16px 0 0;
}
div.courses-items[designitem="gridPrograms"]{
  padding: 0 8px 0 0;
}

.tui-dlSliderItem {
  width: 18%;
}

@media (min-width: 767.98px) {
  .tui-dlGridRowSection {
    margin-left: var(--dl-grid-gap); 
    margin-right: var(--dl-grid-gap); 
    color: #FFF;
  }
}
</style>