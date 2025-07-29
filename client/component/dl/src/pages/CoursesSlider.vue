<template>
  <CourseSlider :limit="limit"/>
</template>

<script>

import CourseSlider from "../components/slider/CourseSlider.vue";

export default {
  components: {
    CourseSlider
  },
  props: ['props'],
  data() {
    return {
      courses: [],
      limit: 4,
    };
  },
  created() {
    const data = JSON.parse(this.props);
    this.limit = data.coursesCount
  }
};
</script>