<template>
  <div class="tui-programsView">
    <LearningItemsBanner :image="bannerImage" />
    <LearningList :items="programs" :headerText="headerText" :buttonText="buttonText"/>
    <p v-if="!loading && !programs.length" class="tui-noProgramsMessage">{{ $str('noenrolledprograms', 'theme_dlcourseflix') }}</p>
    <ComponentLoading v-if="loading" />
    <button v-if="!loading && !allLoaded && programs.length" class="tui-loadMoreButton" @click="fetchPrograms">
        <i class="fas fa-plus"></i>
    </button>
  </div>
</template>

<script>
import LearningItemsBanner from '../components/banner/LearningItemsBanner.vue';
import LearningList from '../components/list/LearningList.vue';
import MyProgramsQuery from 'local_dlservices/graphql/myprograms';
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
          return this.$str('myprograms', 'theme_dlcourseflix');
      },
      buttonText() {
          return this.$str('gotoprogram', 'theme_dlcourseflix');
      }
  },
  data() {
      return {
          programs: [],
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
      this.fetchPrograms();
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    fetchPrograms() {
        if (this.loading || this.allLoaded) return;
        this.loading = true;
        this.$apollo.query({
            query: MyProgramsQuery,
            variables: { searchTerm: this.searchTerm, spage: this.page, limit: this.limit }
        })
        .then(response => {
            const newPrograms = response.data.local_dlservices_myprograms.programs || [];
            if (newPrograms.length === 0) {
                this.allLoaded = true;
            } else {
                this.programs = [...this.programs, ...newPrograms];
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
        if ( bottom > -1) {
            this.fetchPrograms();
        }
    },
    handleSearchEnter(event) {
        if (event.key === 'Enter') {
            this.page = 0;
            this.programs = [];
            this.allLoaded = false;
            const searchTerm = event.target.value.trim();
            this.searchTerm = searchTerm;
            this.fetchPrograms();
        }
    }
  }
};
</script>

<lang-strings>
  {
    "theme_dlcourseflix": [
        "myprograms",
        "gotoprogram",
        "noenrolledprograms"	
    ]
  }
</lang-strings>


<style lang="scss">
.tui-programsView {
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
    .tui-noProgramsMessage {
        text-align: center;
        font-size: 18px;
        color: #666;
        margin-top: 20px;
    }
}
</style>
