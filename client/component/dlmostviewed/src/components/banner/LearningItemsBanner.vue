<template>
    <div class="tui-learningItemsBanner">
        <div class="dl-gradient-overlay"></div>
        <img class = "d-block w-100" :src="image" alt="Banner Image" />
    </div>
</template>

<script>
export default {
  props: {
    image: {
      type: String,
      required: true
    }
  }
};
</script>

<style lang="scss">
.tui-learningItemsBanner {
    position: relative;
    max-height: 400px;
    overflow: hidden;
    margin-left: -64px;
    margin-right: -64px;
    @media screen and (max-width: 1024px) {
        margin-left: -16px;
        margin-right: -16px;
    }
}
</style>
