<template>
  <div>
    <q-form ref="loginForm">
      <q-input
        v-model.trim="email"
        outlined
        label="Your email"
        autocomplete="username"
        :rules="emailRules"
        lazy-rules
        class="input-width-login q-mx-auto q-mb-xs"
      />
      <q-input
        v-model.trim="password"
        outlined
        autocomplete="current-passowrd"
        label="Password"
        :rules="passwordRules"
        lazy-rules
        class="input-width-login q-mx-auto q-mb-xs"
        :type="isPwd ? 'password' : 'text'"
      >
        <template #append>
          <q-icon
            :name="isPwd ? 'mdi-eye-off' : 'mdi-eye'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </q-input>
      <q-input
        v-if="!fromLogin"
        v-model.trim="username"
        outlined
        label="User Name"
        :rules="usernameRules"
        lazy-rules
        class="input-width-login q-mx-auto q-mb-md"
      />
      <div class="q-mx-auto text-center justify-center">
        <div class="row w-100">
          <q-btn
            color="primary"
            class="btn-login"
            unelevated
            :disabled="!isDisabled"
            data-cy="btn-submit-email-signin"
            @click="login"
          >
            <h6
              class="text-weight-bold q-my-none no-transform"
            >
              {{ fromLogin ? 'Log In' : 'Join with Email' }}
            </h6>
          </q-btn>
        </div>
        <p
          v-if="fromLogin"
          data-cy="btn-to-forgot-password"
          class="underline q-mt-lg cursor-pointer"
          :class="$q.screen.width > 1023 ? 'text-white' : 'text-grey'"
          @click="$router.push({ name: 'ForgotPassword' })"
        >
          Forgot password?
        </p>
      </div>
    </q-form>
  </div>
</template>

<script>
import auth from '@/mixins/auth';
import { PROVIDERS } from '@/mixins/providers';
import { set } from '@/mixins/storage';

export default {
  name: 'LoginForm',
  mixins: [auth],
  props: {
    fromLogin: {
      type: Boolean,
      required: true,
    },
  },
  data: () => ({
    email: '',
    password: '',
    username: '',
    isPwd: true,
  }),
  computed: {
    usernameRules() {
      return [this.isValidUsername];
    },
    emailRules() {
      return [(val) => !!val || 'Email is required', this.isValidEmail];
    },
    passwordRules() {
      return [(val) => (val && val.length >= 8) || 'Min. 8 characters'];
    },
    isDisabled() {
      return (this.password.length >= 8) & this.isValidEmail();
    },
  },
  methods: {
    isValidUsername() {
      const regex = /^[a-zA-Z ]*$/;
      if (!this.username || !this.username.length) { return 'Username is required'; }
      return (
        (regex.test(this.username))
        || "Usernames can't contain special characters or numbers"
      );
    },
    isValidEmail() {
      const emailPattern = /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/;
      return emailPattern.test(this.email) || 'Invalid email';
    },
    login() {
      this.$refs.loginForm.validate().then(async (success) => {
        if (success) {
          // Set provider is EMAIL in storage
          set('AuthProvider', PROVIDERS.EMAIL);
          if (this.fromLogin) {
            auth.methods.loginWithEmail(this.email, this.password);
          }
          else { auth.methods.signUpWithEmail(this.email, this.password, this.username); }
        }
      });
    },
  },
};
</script>

<style  lang="scss">
  .input-width-login {
    width: 100% !important;
  }

  .btn-login {
    width: 100%;
    height: 56px;
    border-radius: 8px;
  }
</style>
