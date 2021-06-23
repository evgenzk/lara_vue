import { shallowMount, createLocalVue } from '@vue/test-utils';
import { Quasar } from 'quasar';
import SkeletonLoader from '@/components/ProfileComponents/SkeletonLoader';

const localVue = createLocalVue();
localVue.use(Quasar);

describe('SkeletonLoader', () => {
  it('renders the SkeletonLoader for desktop', () => {
    const wrapper = shallowMount(SkeletonLoader, {
      localVue,
      mocks: {
        $q: {
          screen: {
            width: 1500,
          },
        },
      },
    });
    expect(wrapper).toMatchSnapshot();
  });

  it('renders the SkeletonLoader for mobile', () => {
    const wrapper = shallowMount(SkeletonLoader, {
      localVue,
      mocks: {
        $q: {
          screen: {
            width: 500,
          },
        },
      },
    });
    expect(wrapper).toMatchSnapshot();
  });
});
