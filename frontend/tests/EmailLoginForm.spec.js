import { mount, shallowMount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import {
  Quasar, QInput, QBtn, QForm,
} from 'quasar';
import { __createMocks as createStoreMocks } from '@/store';
import EmailLoginForm from '../EmailLoginForm';
import { emailLoginData } from './data/mockData';

// Mock the implementation of the store
jest.mock('@/store');

const localVue = createLocalVue();
localVue.use(Vuex);
localVue.use(Quasar, { components: [QInput, QBtn, QForm] });

describe('EmailLoginForm', () => {
  const storeMocks = createStoreMocks();
  const methods = {
    login: jest.fn(),
  };

  const shallowMountWrapper = shallowMount(EmailLoginForm, {
    store: storeMocks.store,
    localVue,
    methods,
    listeners: {
      clickLogin: methods.login(),
    },
    computed: {
      emailRules: jest.fn(),
      passwordRules: jest.fn(),
    },
  });

  const wrapper = mount(EmailLoginForm, {
    store: storeMocks.store,
    localVue,
    methods,
    listeners: {
      clickLogin: methods.login(),
    },
  });

  it('Is a Vue instance', async () => {
    expect(shallowMountWrapper.isVueInstance).toBeTruthy();
  });

  it('Renders EmailLoginForm as login', () => {
    const wrapperSignIn = shallowMount(EmailLoginForm, {
      store: storeMocks.store,
      localVue,
      methods,
      listeners: {
        clickLogin: methods.login(),
      },
      computed: {
        emailRules: jest.fn(),
        passwordRules: jest.fn(),
      },
      propsData: {
        fromLogin: true,
      },
    });
    expect(wrapperSignIn).toMatchSnapshot();
  });

  it('Renders EmailLoginForm as signup', () => {
    expect(shallowMountWrapper).toMatchSnapshot();
  });

  it('Expects login to be called', async () => {
    await wrapper.vm.$emit('clickLogin');
    expect(methods.login).toBeCalled();
  });

  it('Test if all required inputs exist', async () => {
    const inputs = wrapper.findAllComponents(QInput);
    expect(inputs).toHaveLength(3);
  });

  it('Expects form validation to fail', async () => {
    const form = wrapper.findComponent(QForm);
    const formSuccess = await form.vm.validate();
    expect(formSuccess).toBeFalsy();
  });

  it('Expects form validation to succeed', async () => {
    const form = wrapper.findComponent(QForm);
    wrapper.setData(emailLoginData);

    await wrapper.vm.$nextTick();

    const formSuccess = await form.vm.validate();
    expect(formSuccess).toBeTruthy();
  });

  it('Expects Log In button to be disabled, invalid email', async () => {
    emailLoginData.email = 'test';
    wrapper.setData(emailLoginData);

    await wrapper.vm.$nextTick();

    expect(wrapper.vm.isDisabled).toBeFalsy();
  });

  it('Expects Log In button to be disabled, password < 8', async () => {
    emailLoginData.email = 'test@best-projekt.com';
    emailLoginData.password = '1234';
    wrapper.setData(emailLoginData);

    await wrapper.vm.$nextTick();

    expect(wrapper.vm.isDisabled).toBeFalsy();
  });

  it('Expects Log In button to be enabled', async () => {
    emailLoginData.email = 'test@best-projekt.com';
    emailLoginData.password = '12345678';
    wrapper.setData(emailLoginData);

    await wrapper.vm.$nextTick();

    expect(wrapper.vm.isDisabled).toBeTruthy();
  });
});
