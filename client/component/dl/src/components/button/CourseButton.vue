<template>
    <div class="tui-dlCourseButton">
        <a :href="link" role="button" aria-label="Navigate to course details" :designitem="designitem">
            <span >{{ buttonText }}</span>
            <img
                loading="lazy"
                src="https://cdn.builder.io/api/v1/image/assets/9586aa98be964eedbf61bacb7b3d9233/9fe48ba186171c3aba617daa0777e4df85ebe1bd054d58f8e10c2c1994ea4a01?apiKey=9586aa98be964eedbf61bacb7b3d9233&"
                class="object-contain tui-dlW6"
                alt=""
                role="presentation"
            />
        </a>
    </div>
</template>

<script lang="ts">
export default {
    props: {
      link: {
        type: String,
        required: true
      },
      designitem: {
        type: String,
        required: true
      }
    },
    computed: {
      buttonText() {
        return this.designitem === 'gridPrograms' ? 'Ir al programa' : 'Ir al curso';
      }
    }
};
</script>

<style lang="scss">
.tui-dlCourseButton {
    margin-top: 8px;
    a {
        display: flex;
        align-items: center;
        padding: 0.3em .6em;
        padding-top: 8px;
        padding-right: 12px;
        padding-bottom: 8px;
        padding-left: 12px;
        background-color: white;
        border-radius: 4px;
        text-align: center;
        color: var(--dl-principal);
        font-family: 'Roboto-Bold', sans-serif;
        font-size: 14px;
        width: max-content;
        &:hover {
            background-color: gray-100;
        }
        &:focus {
            outline: none;
            ring: 2px;
            ring-color: var(--dl-background);
        }
        img {
            margin-left: 4px;
        }
        @media screen and (max-width: 767.98px) {
            padding: 0.3em .6em;
            font-size: 12px;
        }
    }
}
</style>
