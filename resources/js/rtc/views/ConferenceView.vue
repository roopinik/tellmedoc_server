<script>
export default {
  name: "conference-view",
  methods: {
    openSettingModal() {
      this.isOpened = !this.isOpened;
      // this.$modal.show('settings-modal');
    },
  },
  props: ['checkIn','channel'],
  mounted() {
    
  },

  data: function () {
    return {
      isOpened: false,
      settingsModal: null,
      joinFormModal: null,
    };
  },
};
</script>

<template>
  <div class="w-100 h-100 conferece-container text-center" style="">
    <div id="local-player"></div>
    <div id="remote-player" class="w-100 h-100 player"></div>

    <call-controls></call-controls>
  </div>
</template>