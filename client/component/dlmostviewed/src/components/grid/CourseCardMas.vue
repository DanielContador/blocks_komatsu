<template>
  <div
    class="dl-course-card expanded-course-card"
    :style="{ width: cardWidth }"
  >
    <div class="dl-course-card-image">
      <img alt="course" loading="lazy" :src="currentImage" />
      <div class="dlcc-overlay"></div>
    </div>
    <div
      class="dl-card-body"
      :style="{ height: cardBodyHeight, width: cardBodyWidth }"
    >
      <h3 class="dl-course-category">{{ courseCategory }}</h3>
      <div class="dl-play-buttons">
        <div class="dl-play-buttons-container">
          <a class="dl-like-icon" :href="courseLink"
            ><i class="fa fa-play"></i
          ></a>
        </div>
        <div class="dl-like-buttons-container">
          <a class="dl-like-icon" href="#" @click.prevent="toggleLike">
            <i :class="like ? 'fa fa-thumbs-up' : 'fa fa-thumbs-o-up'"></i>
          </a>
        </div>
      </div>
      <div class="dl-course-level">
        <p>
          Nivel: <span>&nbsp{{ courseLevel }}</span>
        </p>
        <div class="dl-course-time">
          <p>{{ courseTime }}</p>
        </div>
      </div>
      <p class="dl-course-name">{{ courseName }}</p>
    </div>
    <div
      v-if="!isExpanded"
      :class="['dl-top-tag', { 'z-index-negative': !courseTop }]"
    >
      <p>TOP</p>
      <p>{{ courseTop }}</p>
      <div class="dl-rotate"></div>
    </div>
    <div
      v-if="!isExpanded"
      :class="[
        'dl-recent-course',
        { 'z-index-negative': !recentCourse || gridProgress },
      ]"
    >
      <p>Recién agregado</p>
    </div>
    <div
      v-if="progressCourse && !isExpanded && !gridProgress"
      class="dl-course-percent-bar"
    >
      <div class="dl-fill-progress" :style="{ width: progressBarWidth }"></div>
    </div>
    <div
      class="dlcc-progress-bar-overlay"
      v-if="progressCourse && !isExpanded && gridProgress"
    >
      <div class="dlcc-progress-bar" :style="{ width: progressBarWidth }"></div>
      <p class="dlcc-progress-text text-center">{{ progressBarWidth }}</p>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  data() {
    return {
      like: false,
      courseImage: "https://www.gstatic.com/webp/gallery/1.webp",
      courseGif: "",
      courseCategory: "Categoría",
      courseLevel: "Básico",
      courseTime: "No definido",
      courseName: "Nombre del Curso",
      courseTop: 0,
      recentCourse: true,
      progressCourse: 50,
      unstyledName: "Primera Línea Segunda línea Tercera Linea",
      courseLink: "#",

      gridProgress: true,
    };
  },
  props: {
    item: {
      type: Object,
      required: true,
      validator: function(obj) {
        return [
          "id",
          "category",
          "shortname",
          "fullname",
          "link",
          "imageUrl",
          "duration",
        ].every((prop) => obj.hasOwnProperty(prop));
      },
    },
    showInfo: {
      type: Boolean,
      required: true,
    },
    isFirst: {
      type: Boolean,
      required: true,
    },
    isLast: {
      type: Boolean,
      required: true,
    },
    designitem: {
      type: String,
      required: true,
    },
    id: {
      type: String,
      required: true,
    },
  },
  mounted() {
    this.courseCategory = this.item.category;
    this.courseLevel = this.item.level || "Básico";
    this.courseTime = this.item.duration || "No definido";
    this.courseName = this.item.fullname;
    this.courseImage = this.item.imageUrl;
    this.courseLink = this.item.link;
    this.unstyledName = this.item.fullname;
    this.progressCourse = this.item.progress;
    this.courseTop = this.item.top;
    this.courseGif = this.item.gifImage;
    this.recentCourse = this.item.recent;
    if (this.designitem != "gridInProgress") {
      this.progressCourse = 0;
      this.gridProgress = false;
    }
  },
  computed: {
    currentImage() {
      return this.courseGif || this.courseImage;
    },
    cardBodyHeight() {
      // Devuelve siempre el valor expandido
      return "170px";
    },
    cardBodyWidth() {
      // Devuelve siempre el valor expandido
      return "314.5px";
    },
    cardWidth() {
      // Devuelve siempre el valor expandido
      return "315px";
    },
    formattedLines() {
      const arrNames = this.unstyledName.split(" ");
      const lines = ["", "", ""];

      arrNames.forEach((name) => {
        if (name.length <= 15) {
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].length + name.length + (lines[i] ? 1 : 0) <= 13) {
              lines[i] += (lines[i] ? " " : "") + name;
              break;
            }
          }
        }
      });

      return lines;
    },
    progressBarWidth() {
      if (this.progressCourse) {
        return this.progressCourse + "%";
      } else {
        return "0%";
      }
    },
  },
  methods: {
    toggleLike() {
      this.like = !this.like;
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap");
@import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap");
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css");

.dl-course-card {
  background-color: #06032f;
  width: 278px;
  height: 158px;
  border-radius: 5px;
  transition-duration: 0.5s;
  box-shadow: 1px 2px 2px 0 #06032f;
}

.dl-course-card:hover {
  border-bottom-right-radius: unset;
  border-bottom-left-radius: unset;
}

.dl-course-card-image {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 5px;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
  }
}

.dl-top-tag {
  position: relative;
  bottom: 158px;
  left: 238px;
  width: 40px;
  height: 46px;
  background-color: #ffc82f;
  text-align: center;
  border-top-right-radius: 5px;
  padding-top: 15px;

  p {
    font-family: "Open Sans", serif;
    letter-spacing: 0;
    line-height: 0.3;
    color: #140a9a;
    font-weight: bold;
  }

  .dl-rotate {
    transform: skewY(8deg);
    background-color: #ffc82f;
    width: 100%;
    height: 8px;
  }
}

.dl-course-dynamic {
  width: 50%;
  height: 65px;
  position: relative;
  bottom: 140px;
  left: 75px;
  text-align: center;

  p {
    font-family: "Montserrat", serif;
    text-transform: uppercase;
    line-height: 0.5;
    letter-spacing: 0;
  }

  p:first-of-type {
  }

  p:nth-of-type(2) {
    color: #ffc82f;
  }

  p:nth-of-type(3) {
    color: #ffffff;
  }
}

.dl-recent-course {
  width: 125px;
  height: 21px;
  background-color: #ffc82f;
  position: relative;
  bottom: 132px;
  left: 80px;
  text-align: center;
  align-content: center;
  border-top-left-radius: 5px;
  border-bottom-right-radius: 5px;
  padding-bottom: 4px;

  p {
    font-size: 1rem;
    font-family: "Open Sans", serif;
    font-weight: bold;
    color: #140a9a;
    margin: 0;
    padding-top: 4px;
  }
}

.dl-course-percent-bar {
  background-color: #6e757a;
  position: relative;
  width: 162px;
  height: 5px;
  bottom: 120px;
  left: 60px;
  border-radius: 5px;

  .dl-fill-progress {
    background-color: #ffc82f;
    width: 50%;
    height: 5px;
    border-radius: 5px;
  }
}

.dl-card-body {
  background-color: #06032f;
  width: 95%;
  height: 0;
  transition-duration: 0.5s;
  overflow: hidden;
  padding-left: 15px;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  box-shadow: 1px 2px 2px 0 #06032f;

  .dl-play-buttons-container {
    background-color: white;
    width: 35px;
    border-radius: 50%;
    height: 35px;
    text-align: center;
    align-content: center;
  }

  .dl-like-buttons-container {
    background-color: #2c3e50;
    width: 35px;
    border-radius: 50%;
    height: 35px;
    text-align: center;
    align-content: center;
    border: 2px solid #ffffff;
    margin-left: 10px;
  }

  .dl-play-buttons {
    display: flex;
    margin-left: 5px;
  }

  .dl-play-buttons-container i {
    color: #2c3e50;
    font-size: 1.5rem;
    padding-top: 3px;
    padding-left: 3px;
  }

  .dl-like-buttons-container i {
    color: #ffffff;
    font-size: 1.5rem;
  }

  .dl-course-category,
  .dl-course-name {
    color: #ffffff;
  }

  .dl-course-level {
    display: flex;
    margin-top: 5px;
    color: #ffc82f;
    font-weight: bold;
  }

  .dl-course-time {
    background-color: transparent;
    border: 2px solid #ffffff;
    color: #ffffff;
    margin-left: 5px;

    p {
      margin: 0;
    }
  }
}

.z-index-negative {
  z-index: -1;
}

.expanded-course-card {
  /* Estilos que ya tenías */
  position: absolute;
  top: 0;
  left: 0;
  width: 315px;
  transform: scale(1.1);
  z-index: 100;
  transition: transform 0.3s, opacity 0.3s, visibility 0.3s; /* Añade opacity y visibility a la transición */

  /* 👇 AÑADE ESTO PARA OCULTARLO POR DEFECTO 👇 */
  opacity: 0;
  visibility: hidden;
  pointer-events: none; /* Evita que intercepte el ratón cuando está oculto */
}

.dlcc-progress-bar-overlay {
  background-color: transparent;
  position: relative;
  border: 2px solid;
  bottom: 150px;
  width: 278px;
  border-bottom-left-radius: 5px;
  border-bottom-right-radius: 5px;
  height: 18px;
}

.dlcc-progress-bar {
  background-color: #ffffff;
  height: 100%;
}

.dlcc-overlay {
  position: relative;

  width: 100%;

  height: 100%;

  position: absolute;

  top: 0;
  left: 0;

  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 20%, #1b156d 90%);

  pointer-events: none; /* deja clics al <img> si quieres */
}

.dlcc-progress-text {
  position: relative;
  bottom: 17px;
  mix-blend-mode: difference;
  color: white;
}

.expanded-course-card.is-hovered {
  opacity: 1;
  visibility: visible;
  pointer-events: auto; /* Permite la interacción cuando está visible */
}
</style>
