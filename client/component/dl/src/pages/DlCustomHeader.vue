<template>
  <div id="dlch-overlay" class="dlch-overlay">
    <div id="dlch-container" class="dlch-container"
         :style="{backgroundImage: currentImage, position: togglePosition}"></div>
    <div class="dlch-page">
      <p>Mis Cursos</p>
    </div>
    <div class="dlch-text">
      <p>{{ this.text }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import CourseButton from "../components/button/CourseButton.vue";

export default {
  components: {CourseButton},
  props: ['props'],
  data() {
    return {
      isSlide: false,
      currentSlide: 0,
      previousSlide: 0,
      slideInterval: null,
      text: '',
      editingBlocks: false,
      images: [{
        src: '',
        text: ''
      }]
    }
  },
  created() {
    const data = JSON.parse(this.props);
    this.isSlide = data.isSlide
    this.images = data.images
    this.editingBlocks = data.editingBlocks
  },
  computed: {
    currentImage() {
      this.text = this.images[0].text
      return 'url(' + this.images[0].src + ')'
    },
    togglePosition() {
      return this.editingBlocks ? 'relative' : 'absolute'
    }
  }
}
</script>

<style lang="scss">
.dlch-container {
  position: absolute;
  top: 0;
  width: 124%;
  height: 421px;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 20%, #06032F 90%);
  margin-left: -13%;
}

.dlch-overlay {
  height: 450px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  flex-direction: column;
}

.dlch-imageContainer img,
.dlch-imageContainer,
.dlch-item,
.dlch-inner,
.dlch-slide {
  height: 100%;
}

.dlch-text {
  z-index: 1;
  font-family: 'Roboto Regular', serif;
  font-size: 20px;
  line-height: 20px;
  letter-spacing: 0;
  width: 90%;
}

.dlch-page {
  z-index: 1;
  margin-bottom: 60px;
  font-family: 'Roboto', serif;
  font-weight: bold;
  font-size: 36px;
  line-height: 58.3px;
  letter-spacing: 0;
  align-items: flex-start;
  align-content: flex-start;
  align-self: flex-start;
  padding-left: 20%;
}

</style>