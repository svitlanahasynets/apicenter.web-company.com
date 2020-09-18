<script>
  const default_layout = "default";
  import PageTitle from "../../Layout/Components/PageTitle.vue";
  import {library} from '@fortawesome/fontawesome-svg-core'
  import {
      faTrashAlt,
      faCheck,
      faCalendarAlt,
      faAngleDown
  } from '@fortawesome/free-solid-svg-icons'
  import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

  library.add(
      faTrashAlt,
      faCheck,
      faAngleDown,
      faCalendarAlt,
  );
  export default {
    props: ['postTitle'],
     data: function () {
      return {
        heading: this.postTitle,
        icon: 'pe-7s-tools icon-gradient bg-mixed-hopes',
      }
    },
    computed: {
      layout() {
        return default_layout + '-layout';
      },
    },
    components: {
        PageTitle,
        'font-awesome-icon': FontAwesomeIcon,
    },
    // created: function() {
    //   console.log(this.postTitle);
    // }
  }
</script>
