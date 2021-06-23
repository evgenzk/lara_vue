<template>
  <q-dialog
    ref="centerToastDialog"
    v-model="show"
    content-style="background: rgba(0, 47, 67, 0.7)"
  >
    <q-card
      class="center-toast-wrapper overflow-hidden fixed-center q-mx-auto shadow-0"
    >
      <div class="row items-center q-mx-md" style="height: 100%">
        <div class="col-grow">
          <h6 class="text-secondary q-my-none text-weight-bold">{{ text }}</h6>
        </div>
        <div class="col-shrink">
          <q-icon size="20px" class="center-toast-icon" name="mdi-check" />
        </div>
      </div>
    </q-card>
  </q-dialog>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'CenterToast',
  props: {
    text: {
      type: String,
      required: true,
    },
  },
  data: () => ({
    show: true,
  }),
  computed: {
    ...mapGetters(['centerToast']),
  },
  mounted() {
    const timeout = this.centerToast.timeout && this.centerToast.timeout > 0
      ? this.centerToast.timeout
      : 2000;
    setTimeout(() => {
      this.$emit('dismiss');
    }, timeout);
  },
};
</script>

<style lang="scss" scoped>
  .center-toast-wrapper {
    width: 280px;
    height: 70px;
    box-shadow: 0px 2px 60px rgba(5, 219, 137, 0.6) !important;
    border-radius: 16px !important;
  }

  .alert-title {
    box-shadow: 0px 2px 60px rgba(5, 219, 137, 0.6);
    border-radius: 16px;
    font-size: 14px;
    line-height: 21px;
    width: 295px;
    height: 80px;
  }

  .center-toast-icon {
    color: $app-white;
    background: $app-green;
    border-radius: 50%;
    height: 34px;
    width: 34px;
    margin: initial;
  }
</style>
