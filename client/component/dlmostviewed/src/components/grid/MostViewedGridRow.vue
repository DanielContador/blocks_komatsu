<template>
  <section role="region" class="mb-12 tui-dlGridRowSection">
    <ComponentLoading v-if="loadingCoursesMoreViews" />
    <div
      v-else
      v-for="(categoryGroup, categoryName) in groupedCoursesMoreViews"
      :key="categoryName"
    >
      <p class="tui-dlCourseCategoryHeading">
        Los más vistos <i class="ml-4 fa-light fa-chevron-right"></i>
      </p>
      <div
        :class="[
          'tui-dlGridRow',
          {
            'tui-dlItemHovered':
              hoveredIndex !== null && hoveredCategory === categoryName,
            'row-is-expanded':
              hoveredIndex !== null && hoveredCategory === categoryName,
          },
        ]"
      >
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
        <div
          class="tui-dlGridRowContent"
          :ref="'gridRowContent-' + categoryName"
        >
          <div
            v-for="(course, index) in categoryGroup"
            :key="course.id"
            class="tui-dlGridItemContainer more-views-courses"
            @mouseenter="onMouseEnter(categoryName, index)"
            @mouseleave="onMouseLeave"
          >
            <CourseCardMas
              :key="'card-' + course.id"
              :item="course"
              :showInfo="true"
              :index="index"
              :isFirst="firstItemIndex === index"
              :isLast="lastItemIndex === index"
              designitem="more-views"
              :id="'course-card-' + course.id"
              :class="{
                'is-hovered':
                  hoveredCategory === categoryName && hoveredIndex === index,
              }"
            />
            <GridMostViewItem
              v-if="validateCourse(course)"
              :key="'item-' + course.id"
              :item="course"
              :index="index"
              :isFirst="firstItemIndex === index"
              :isLast="lastItemIndex === index"
              :is-any-item-hovered="
                hoveredCategory === categoryName && hoveredIndex !== null
              "
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
</template>

<script>
import CourseCardMas from "./CourseCardMas.vue";
import GridMostViewItem from "./GridMostViewItem.vue";
import CoursesMoreViewQuery from "local_dlservices/graphql/moreviewsitems";
import AllCoursesByCategoryQuery from "local_dlservices/graphql/items";
import ComponentLoading from "tui/components/loading/ComponentLoading";

export default {
  components: {
    CourseCardMas,
    GridMostViewItem,
    ComponentLoading,
  },
  data() {
    return {
      timer: null,
      hoveredIndex: null,
      hoveredCategory: null,
      firstItemIndex: null,
      lastItemIndex: null,
      coursesmoreviews: [],
      courses: [],
      groupedCourses: null,
      groupedCoursesMoreViews: null,
      // designitem:'',
      design: "",
      searchTerm: "",
      loadingGroupedCourses: false,
      loadingCoursesMoreViews: false,
    };
  },
  created() {
    document
      .getElementById("genericsearchbox")
      ?.addEventListener("keyup", this.handleSearchEnter);
    this.fetchMoreViewCourses();
    this.fetchGroupedCourses();
  },
  methods: {
    getItemStep(el) {
      // primer item visible
      const item = el.querySelector(".tui-dlGridItemContainer");
      if (!item) return 1;

      const style = getComputedStyle(item);
      const gap = parseFloat(style.marginRight || 0); // tu separación horizontal
      const width = item.clientWidth;

      return width + gap; // tamaño real del “paso” horizontal
    },
    getFirstVisibleIndex(el) {
      const step = this.getItemStep(el);
      return Math.floor(el.scrollLeft / step);
    },

    scrollRight(categoryName) {
      // Resetea el estado del hover inmediatamente.
      this.hoveredCategory = null;
      this.hoveredIndex = null;

      this.$nextTick(() => {
        let el = this.$refs[`gridRowContent-${categoryName}`];
        el = Array.isArray(el) ? el[0] : el;
        if (!el) return;

        const amount = el.clientWidth * 0.8;
        const startPosition = el.scrollLeft;
        const targetPosition = startPosition + amount;

        // Usa la función de scroll manual en lugar de la nativa.
        this.smoothScroll(el, startPosition, targetPosition);
      });
    },

    scrollLeft(categoryName) {
      // Resetea el estado del hover inmediatamente.
      this.hoveredCategory = null;
      this.hoveredIndex = null;

      this.$nextTick(() => {
        let el = this.$refs[`gridRowContent-${categoryName}`];
        el = Array.isArray(el) ? el[0] : el;
        if (!el) return;

        const amount = el.clientWidth * 0.8;
        const startPosition = el.scrollLeft;
        const targetPosition = startPosition - amount;

        // Usa la función de scroll manual en lugar de la nativa.
        this.smoothScroll(el, startPosition, targetPosition);
      });
    },

    onMouseEnter(categoryName, index) {
      // Simplemente actualiza el estado. Nada más.
      this.hoveredCategory = categoryName;
      this.hoveredIndex = index;
    },
    onMouseLeave() {
      this.hoveredCategory = null;
      this.hoveredIndex = null;
    },
    fetchGroupedCourses() {
      if (this.loadingGroupedCourses) return;
      this.loadingGroupedCourses = true;
      this.$apollo
        .query({
          query: AllCoursesByCategoryQuery,
          variables: {
            searchTerm: this.searchTerm,
            spage: 0,
          },
        })
        .then((response) => {
          const items = response.data.local_dlservices_items.items || [];
          this.groupedCourses = items.reduce((acc, course) => {
            const categoryName = course.category || "Sin categoría";
            if (!acc[categoryName]) {
              acc[categoryName] = [];
            }
            acc[categoryName].push(course);
            return acc;
          }, {});
          this.courses = items;
          this.loadingGroupedCourses = false;
        })
        .catch((error) => {
          console.error("Error fetching courses:", error);
          this.loadingGroupedCourses = false;
        });
    },
    fetchMoreViewCourses() {
      if (this.loadingCoursesMoreViews) return;
      this.loadingCoursesMoreViews = true;
      this.$apollo
        .query({
          query: CoursesMoreViewQuery,
          variables: {
            limit: 10,
            recommended: false,
            design: "",
          },
        })
        .then((response) => {
          const items =
            response.data.local_dlservices_moreviewsitems.items || [];
          this.groupedCoursesMoreViews = items.reduce((acc, course) => {
            const categoryName = "moreviews";
            if (!acc[categoryName]) {
              acc[categoryName] = [];
            }
            acc[categoryName].push(course);
            return acc;
          }, {});
          this.coursesmoreviews = items;
          this.loadingCoursesMoreViews = false;
        })
        .catch((error) => {
          console.error("Error fetching courses:", error);
          this.loadingCoursesMoreViews = false;
        });
    },
    validateCourse(course) {
      const requiredProps = [
        "id",
        "category",
        "shortname",
        "fullname",
        "link",
        "imageUrl",
        "duration",
      ];
      const missingProps = requiredProps.filter(
        (prop) => !course.hasOwnProperty(prop) || course[prop] === undefined
      );
      return missingProps.length <= 0;
    },

    smoothScroll(container, startPosition, targetPosition) {
      const duration = 500;
      const startTime = performance.now();

      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Funci�n de easing
        const easeProgress = 1 - Math.pow(1 - progress, 3);

        container.scrollLeft =
          startPosition + (targetPosition - startPosition) * easeProgress;

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
      if (event.key === "Enter") {
        const searchTerm = event.target.value.trim();
        this.searchTerm = searchTerm;
        this.fetchGroupedCourses();
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.tui-componentLoading .tui-iconLoading {
  font-size: 2.6rem;
}

.tui-dlCourseCategoryHeading {
  font-size: 20px;
  font-family: "Roboto-Bold", sans-serif;
  line-height: 34px;
}

.tui-dlCourseCategoryHeading.categories {
  margin-top: 65px;
}

.tui-dlGridRow {
  display: flex;
  position: relative;
  width: 100%;
  padding: 0 var(--dl-grid-gap);
  box-sizing: content-box;

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
      color: #fff;
    }

    &.tui-dlHandlePrev {
      left: 0;
      background: linear-gradient(to left, rgba(0, 0, 0, 0) 0%, #06032f 100%);
    }

    &.tui-dlHandleNext {
      right: 0;
      background: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, #06032f 100%);
    }
  }
}

.tui-dlGridRowContent {
  scroll-behavior: smooth;
  /*  👇  lo importante */
  overflow-x: auto;
  /* Se permite scroll horizontal */
  overflow-y: visible;
  display: inline-flex;
  /* Opcional: ocultar la barra sin desactivar el scroll */
  -ms-overflow-style: none;
  /* IE / Edge */
  scrollbar-width: none;
  /* Firefox */
}

.tui-dlGridRowContent::-webkit-scrollbar {
  /* Chrome */
  display: none;
}

.tui-dlGridRowContent {
  white-space: nowrap;
  margin-left: calc(-1 * var(--dl-grid-gap));
  /* Extend into the left margin */
  margin-right: calc(-1 * var(--dl-grid-gap));
  /* Extend into the right margin */
  padding-left: var(--dl-grid-gap);
  /* Adjust padding to compensate for negative margin */
  padding-right: var(--dl-grid-gap);
  /* Adjust padding to compensate for negative margin */
}

.tui-dlGridItemContainer {
  height: auto;
  min-height: 158px;
  position: relative;
  display: inline-block;
  width: 281.37px;
  white-space: normal;
  transition: transform 0.3s ease-in-out, z-index 0.3s;
  flex-shrink: 0;
}

.tui-dlGridItemContainer.more-views-courses {
  height: 185px;
}

.tui-dlGridItemContainer,
.tui-dlGridItem2 {
  overflow: visible !important;
}

.more-views-courses {
  padding: 0;
}

.courses-items {
  /*padding: 0 .2vw;*/
  padding: 0 16px 0 0;
}

div.courses-items[designitem="gridPrograms"] {
  padding: 0 8px 0 0;
}

.tui-dlSliderItem {
  width: 18%;
}

@media (min-width: 767.98px) {
  .tui-dlGridRowSection {
    margin-left: var(--dl-grid-gap);
    margin-right: var(--dl-grid-gap);
    color: #fff;
  }
}

.tui-dlGridItemContainer:hover .tui-dlGridItem2 {
  z-index: 10;
  transition: transform 0.3s ease-in-out;
}

.tui-dlGridRow .tui-dlGridItemContainer:first-child:hover .tui-dlGridItem2 {
  transform-origin: top left;
}

.tui-dlGridRow .tui-dlGridItemContainer:last-child:hover .tui-dlGridItem2 {
  transform-origin: top right;
}

/* 1) zoom */
.tui-dlGridItem2:hover {
  overflow: visible;
  /* deja ver la info que está fuera del 160px */
}

.tui-dlGridItem2:hover .tui-dlGridItem2 {
  /* si tu escala está en .tui-dlGridItem2 */
  z-index: 10;
}

/* 2) info-box */
.tui-dlGridItem2:hover .tui-dlCourseInfoContainer {
  display: flex;
  /* o block, según tu diseño */
}

.tui-dlGridRow .tui-dlGridItemContainer:first-child:hover .tui-dlGridItem2 {
  transform-origin: top left;
}

.tui-dlGridRow .tui-dlGridItemContainer:last-child:hover .tui-dlGridItem2 {
  transform-origin: top right;
}

/* La sección entera necesita overflow visible */
.tui-dlGridRowSection {
  position: relative;
  /* para que los absolutos se midan aquí */
  overflow: visible !important;
}

/* La fila también */
.tui-dlGridRow {
  overflow: visible !important;
}

/* El contenedor scroll mantiene solo scroll horizontal */
.tui-dlGridRowContent {
  overflow-x: auto;
  /* horizontal OK */
  overflow-y: visible !important;
  /* vertical visible */
}

/* EN GridRow.vue, AÑADE ESTA NUEVA CLASE */

.tui-dlGridRow.row-is-expanded {
  /* Puedes ajustar este valor. Usa la altura que necesites para la tarjeta expandida. */
  height: 400px;
  transition: height 0.3s ease-in-out; /* Opcional: para una animación suave */
}
</style>
