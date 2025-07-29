<template>
    <div class="tui-learningCard">
      <!-- Imagen del curso -->
      <div class="tui-learningCard__image" v-if="item.imageUrl">
        <img :src="item.imageUrl" :alt="item.fullname" />
      </div>
  
      <!-- Sección central: título y descripción -->
      <div class="tui-learningCard__info">
        <h4 class="fw-bold">{{ item.fullname }}</h4>
        <p v-show="showDescription || !isMobile">{{ item.summary }}</p>
        <button v-if="!showDescription && isMobile && item.summary" @click="showDescription = true" class="tui-showMoreButton">Ver Más ...</button>
      </div>
  
      <!-- Sección derecha: estado, progreso y botón -->
      <div :class="['tui-learningCard__actions', item.itemType === 'program' ? 'tui-programActions' : 'tui-courseActions']">
        <a :href="item.link" class="tui-learningCard__button">
           {{ buttonText }} <i class="fas fa-chevron-right"></i>
        </a>
        <div class="tui-learningCard__progressStatus">
          <div class="tui-learningCard__progressBar">
            <div
              class="tui-learningCard__progressFill"
              :style="{ width: item.progress + '%' }"
            ></div>
          </div>
          <span>{{ item.progress }}% - {{ item.status }}</span>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'LearningCard',
    data() {
      return {
        showDescription: false,
        isMobile: window.innerWidth <= 767.98
      };
    },
    props: {
      item: {
        type: Object,
        required: true
      },
      buttonText: {
        type: String,
        required: true
      }
    },
    mounted() {
      window.addEventListener('resize', this.checkMobileView);
    },
    beforeDestroy() {
      window.removeEventListener('resize', this.checkMobileView);
    },
    methods: {
      checkMobileView() {
        this.isMobile = window.innerWidth <= 767.98;
      }
    }
  };
  </script>

<lang-strings>
{
    "theme_dlcourseflix": [
    "gotocourse"
    ]
}
</lang-strings>
  
<style lang="scss">
.tui-learningCard {
  /* 
      Usamos un grid de 3 columnas:
      - Columna 1: Imagen
      - Columna 2: Título/Descripción
      - Columna 3: Progreso/Estado/Botón
  */
  display: grid;
  grid-template-columns: auto 1fr auto;
  column-gap: 1.5rem;

  /* Espaciado vertical y la línea gris inferior */
  padding: 2.5rem 0;
  border-bottom: 1px solid #ccc; 

  /* Elimina la línea en el último elemento */
  &:last-child {
      border-bottom: none;
  }

  /* Imagen */
  &__image {
      img {
      width: 178px;
      height: 100px;
      object-fit: cover;
      border-radius: 4px;
      }
  }

  /* Título y descripción */
  &__info {
      h4 {
        margin: 0 0 0.25rem;
        color: #fff; /* Ajusta según tu color de texto */
        @media screen and (max-width: 767.98px) {
          font-size: 1.5rem; /* Ajusta según tu tamaño de fuente */
        }
      }
      p {
        margin: 0;
        color: #ccc; /* Ajusta según tu color de texto */
        max-height: 80px;
        overflow: hidden;
        @media screen and (max-width: 767.98px) {
          font-size: 12px; /* Ajusta según tu tamaño de fuente */
          max-height: fit-content;
        }
      }
      .tui-showMoreButton {
        background-color: var(--dl-principal);
        color: #fff;
        border: none;
        padding: 0.3rem 0.6rem;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.875rem;
        transition: background-color 0.3s ease;
        @media screen and (min-width: 768px) {
          display: none;
        }
        &:hover {
          background-color: var(--dl-principal2);
          color: var(--dl-principal);
        }
      }
  }

  /* Sección de acciones (progreso + botón) */
  &__actions {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      min-width: 150px;
      align-self: center;

      /* Progreso y estado */
      &__progressStatus,
      .tui-learningCard__progressStatus {
        margin-bottom: 0.5rem;
        text-align: right;
        span {
            color: #fff; /* Ajusta según tu color de texto */
            @media screen and (max-width: 767.98px) {
              font-size: 12px; /* Ajusta según tu tamaño de fuente */
            }
        }
      }

      /* Barra de progreso */
      &__progressBar,
      .tui-learningCard__progressBar {
        width: 120px;
        height: 4px;
        background-color: #FFFFFF80;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.25rem;
        box-shadow: 0px 0px 7.02px 0.76px #75E06480;
        @media screen and (max-width: 767.98px) {
            width: 100px;
        }

        &__progressFill,
        .tui-learningCard__progressFill {
            height: 100%;
            background: linear-gradient(90deg, #2BB673 0%, #99CCFF 100%);
            transition: width 0.3s;
        }
      }

      /* Botón "Ir al curso" */
      &__button,
      .tui-learningCard__button {
        margin: 6px 0;
        background-color: #FFF;
        color: var(--btn-prim-accent-color);
        text-decoration: none;
        padding: 0.8rem 1rem;
        border-radius: 4px;
        align-self: start;

        @media screen and (max-width: 767.98px) {
          padding: 0.5rem .8rem;
          font-size: 12px;
        } 
        
        &:hover {
            background-color: #4f4f85;
        }
      }

      &.tui-programActions {
        .tui-learningCard__progressBar {
          width: 120px;
          @media screen and (max-width: 767.98px) {
            width: 100px;
          }
        }
      }
  
      &.tui-courseActions {
        .tui-learningCard__progressBar {
          width: 95px;
          @media screen and (max-width: 767.98px) {
            width: 80px;
          }
        }
      }
  }

  @media (max-width: 767.98px) {
    grid-template-columns: auto 1fr;
    row-gap: 1rem;

    &__actions {
      margin-top: 1rem;
    }
  }
  
}
</style>
