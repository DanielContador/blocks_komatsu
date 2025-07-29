<template>
  <div class="tui-learningList">
    <h2 class="fw-bold mb-5">{{ headerText }}</h2>
    <LearningCard
      v-for="item in items"
      :key="item.id"
      :item="item"
      :buttonText="buttonText"
    />
  </div>
</template>

<script>
import LearningCard from './LearningCard.vue';

export default {
  name: 'LearningList',
  components: {
    LearningCard
  },
  props: {
    items: {
      type: Array,
      required: true
    },
    headerText: {
      type: String,
      required: true
    },
    buttonText: {
      type: String,
      required: true
    }
  }
};
</script>

<style lang="scss">
.tui-learningList {
  width: 80%;
  margin: -70px auto 0 auto;
  position: relative;

  h2 {
    @media (max-width: 767.98px) {
      font-size: 2rem;
    }
  }

  @media (max-width: 767.98px) {
    width: 90%;
    margin: -50px auto 0 auto;
  }
}
</style>
