<template>
    <div class="tui-dlCourseCatalog">
   <CourseSlider :limit="limit"  />
   <CourseCatalogGrid />
  </div>
</template>

<script>
import CourseSlider from '../components/slider/CourseSlider.vue';
import CourseCatalogGrid from '../components/grid/CourseCatalogGrid.vue';
import MoreViewQuery from 'local_dlservices/graphql/moreviewsitems';
// import { recommendCommands } from 'yargs';

export default {
  components: {
    CourseSlider,
    CourseCatalogGrid
  },
  data() {
    return {
      courses: [],
      limit: 4,
    };
  },
};
</script>

<lang-strings>
  {
    "totara_tui": [
      "pluginname"
    ]
  }
</lang-strings>

<style lang="scss">
.tui-dlCourseCatalog {
  background-color: #06032F;
  padding-bottom: 40px;
}
</style>
