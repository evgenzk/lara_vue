<template>
  <div v-show="shouldShow" class="q-pl-md">
    <q-item
      v-ripple
      class="q-py-sm q-px-none"
      clickable
      @click="navigateForward()"
    >
      <q-item-section class="q-px-none" avatar>
        <q-img
          v-if="item.entity === 'user'"
          class="result-img rounded object-fit"
          :src="`${staticURL}/image/user/profile/${item.id}/profile.png`"
          @load="setLoaded"
          @error="setLoaded"
        />
        <q-img
          v-else
          class="result-img object-fit"
          :src="staticURL + `/image/plan/banners/${item.id}/plan.png`"
          cover
          @load="setLoaded"
          @error="setLoaded"
        />
      </q-item-section>
      <q-item-section class="q-ml-md">
        <h6
          class="q-my-none ellipsis w-100 text-secondary text-weight-bold"
          style="line-height: 22px"
        >
          {{ item.name }}
        </h6>
        <p
          v-if="item.entity === 'plan'"
          class="q-my-none ellipsis w-100"
        >
          {{ item.user.name }}
        </p>
        <q-list>
          <PlanTag v-for="tag in item.tags" :key="tag.id" :tag="tag.name" />
        </q-list>
      </q-item-section>
    </q-item>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'SearchResult',
  components: {
    PlanTag: () => import('@/components/PlanComponents/PlanTag'),
  },
  props: {
    item: {
      type: Object,
      required: true,
      default: () => {},
    },
  },
  data: () => ({
    tags: [],
    imageLoaded: false,
  }),
  computed: {
    ...mapGetters(['staticURL', 'appMeta']),
    shouldShow() {
      return this.item.name && this.item.name.length && this.imageLoaded;
    },
  },
  methods: {
    setLoaded() {
      this.imageLoaded = true;
    },
    navigateForward() {
      switch (this.item.entity) {
        case 'user': {
          this.$router.push({
            name: 'User',
            params: {
              userUrlSlug: this.item.urlSlug || this.item.id,
              id: this.item.id,
            },
          });
          break;
        }
        case 'plan': {
          this.$router.push({
            name: 'Plan',
            params: {
              planId: this.item.id,
              planUrlSlug: this.item.urlSlug || this.item.id,
              userId: this.item.user.id,
              userUrlSlug: this.item.user.urlSlug || this.item.user.id,
            },
          });
          break;
        }
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  .result-img {
    border: 1px solid rgba(230, 238, 241, 0.5);
    box-sizing: border-box;
    border-radius: 8px;
    height: 64px;
    width: 64px;
  }

  .rounded {
    border-radius: 50% !important;
  }

  .object-fit {
    object-fit: cover !important;
  }
</style>
