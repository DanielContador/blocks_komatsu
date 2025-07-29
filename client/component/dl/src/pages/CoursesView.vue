<template>
    <div class="tui-coursesView">
        <LearningItemsBanner :image="bannerImage" />
        <LearningList v-if="courses.length" :items="courses" :headerText="headerText" :buttonText="buttonText" />
        <p v-if="!loading && !courses.length" class="tui-noCoursesMessage">{{ $str('noenrolledcourses', 'theme_dlcourseflix') }}</p>
        <ComponentLoading v-if="loading" />
        <button v-if="!loading && !allLoaded && courses.length" class="tui-loadMoreButton" @click="fetchCourses">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</template>

<script>
import LearningItemsBanner from '../components/banner/LearningItemsBanner.vue';
import LearningList from '../components/list/LearningList.vue';
import MyCoursesQuery from 'local_dlservices/graphql/mycoursesitems';
import ComponentLoading from 'tui/components/loading/ComponentLoading';

export default {
    components: {
        LearningItemsBanner,
        LearningList,
        ComponentLoading
    },
    props: {
        categoryid: {
            type: Number,
            required: false
        },
        bannerImage: {
            type: String,
            required: true
        }
    },
    computed: {
        headerText() {
            return this.$str('mycourses', 'theme_dlcourseflix');
        },
        buttonText() {
            return this.$str('gotocourse', 'theme_dlcourseflix');
        }
    },
    data() {
        return {
            courses: [],
            page: 0,
            limit: 10,
            searchTerm: '',
            loading: false,
            allLoaded: false
        };
    },
    created() {
        window.addEventListener('scroll', this.handleScroll);
        document.getElementById('genericsearchbox')?.addEventListener('keyup', this.handleSearchEnter);
        this.fetchCourses();
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        fetchCourses() {
            if (this.loading || this.allLoaded) return;
            this.loading = true;
            this.$apollo.query({
                query: MyCoursesQuery,
                variables: { searchTerm: this.searchTerm, spage: this.page, limit: this.limit }
            })
            .then(response => {
                const newCourses = response.data.local_dlservices_mycoursesitems.items || [];
                if (newCourses.length === 0) {
                    this.allLoaded = true;
                } else {
                    this.courses = [...this.courses, ...newCourses];
                    this.page += 1;
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error fetching courses:', error);
                this.loading = false;
            });
        },
        handleScroll() {
            const bottom = (window.innerHeight + window.scrollY) - document.body.offsetHeight;
            if (bottom > -1) {
                this.fetchCourses();
            }
        },
        handleSearchEnter(event) {
            if (event.key === 'Enter') {
                this.page = 0;
                this.courses = [];
                this.allLoaded = false;
                const searchTerm = event.target.value.trim();
                this.searchTerm = searchTerm;
                this.fetchCourses();
            }
        }
    }
};
</script>

<lang-strings>
    {
        "theme_dlcourseflix": [
            "mycourses",
            "gotocourse",
            "noenrolledcourses"
        ]
    }
</lang-strings>
  

<style lang="scss">
.tui-coursesView {
    margin-left: -16px;
    margin-right: -16px;

    .tui-loadMoreButton {
        display: block;
        margin: 20px auto;
        padding: 5px 15px;
        font-size: 20px;
        background-color: var(--dl-principal);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;

        &:hover {
            opacity: 0.8;
        }
    }

    .tui-noCoursesMessage {
        text-align: center;
        font-size: 18px;
        color: #666;
        margin-top: 20px;
    }
}
</style>
