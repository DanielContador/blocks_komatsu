<template>
  <div class="tui-courseCatalogGrid">
    <GridRow :columns="5"/>
  </div>
</template>

<script lang="ts">
import GridRow from './GridRow.vue';

export default {
  components: {
    GridRow
  },
  data() {
    return {};
  },
};
</script>

<style scoped>

</style>
