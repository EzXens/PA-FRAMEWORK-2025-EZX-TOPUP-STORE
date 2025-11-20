<!DOCTYPE html>
<html lang="id" data-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login & Register</title>
    @vite('resources/css/app.css')
    <style>
      /* Background full screen */
      body {
        background-image: url('/images/redwhite.gif');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      /* Glass panel effect */
      .glass-panel {
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
      }

      /* Animasi tab slide */
      .fade-slide {
        transition: all 0.5s ease;
      }
      .fade-slide-enter {
        opacity: 0;
        transform: translateY(15px);
      }
      .fade-slide-enter-active {
        opacity: 1;
        transform: translateY(0);
      }
      .fade-slide-leave {
        opacity: 1;
        transform: translateY(0);
      }
      .fade-slide-leave-active {
        opacity: 0;
        transform: translateY(-15px);
      }
    </style>
  </head>
<body data-active-tab="{{ session('auth_tab', 'login') }}">

    <div class="w-full max-w-md glass-panel rounded-2xl p-6">
      <div class="tabs tabs-boxed mb-4 bg-transparent">
        <a id="tab-login" class="tab tab-active">Login</a>
        <a id="tab-register" class="tab">Register</a>
      </div>

      <!-- LOGIN FORM -->
      <div id="login-form" class="fade-slide">
        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
          @csrf
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Email/Username</span>
            </label>
            <input
              type="text"
              name="identifier"
              placeholder="masukkan email atau username"
              class="input input-bordered w-full"
              value="{{ old('identifier') }}"
              required
            />
            @error('identifier', 'login')
              <p class="text-sm text-error mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Password</span>
            </label>
            <input
              type="password"
              name="password"
              placeholder="masukkan password"
              class="input input-bordered w-full"
              required
            />
            @error('password', 'login')
              <p class="text-sm text-error mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div class="flex justify-between items-center text-sm text-gray-200">
          </div>
          <button class="btn btn-primary w-full mt-4">Masuk</button>
        </form>
      </div>

      <!-- REGISTER FORM -->
      <div id="register-form" class="hidden fade-slide">
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
          @csrf
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Username</span>
            </label>
            <input
              type="text"
              name="username"
              placeholder="Username"
              class="input input-bordered w-full"
              value="{{ old('username') }}"
              required
            />
            @error('username', 'register')
              <p class="text-sm text-error mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Email</span>
            </label>
            <input
              type="email"
              name="email"
              placeholder="alamat email"
              class="input input-bordered w-full"
              value="{{ old('email') }}"
              required
            />
            @error('email', 'register')
              <p class="text-sm text-error mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Password</span>
            </label>
            <input
              type="password"
              name="password"
              placeholder="buat password"
              class="input input-bordered w-full"
              required
            />
            @error('password', 'register')
              <p class="text-sm text-error mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">
              <span class="label-text text-black font-bold">Konfirmasi Password</span>
            </label>
            <input
              type="password"
              name="password_confirmation"
              placeholder="ulangi password"
              class="input input-bordered w-full"
              required
            />
          </div>
          <button class="btn btn-secondary w-full mt-4">Daftar</button>
        </form>
      </div>
    </div>

    <script>
      const tabLogin = document.getElementById("tab-login");
      const tabRegister = document.getElementById("tab-register");
      const loginForm = document.getElementById("login-form");
      const registerForm = document.getElementById("register-form");

      tabLogin.addEventListener("click", () => {
        tabLogin.classList.add("tab-active");
        tabRegister.classList.remove("tab-active");
        registerForm.classList.add("hidden");
        loginForm.classList.remove("hidden");
        animateSwitch(loginForm);
      });

      tabRegister.addEventListener("click", () => {
        tabRegister.classList.add("tab-active");
        tabLogin.classList.remove("tab-active");
        loginForm.classList.add("hidden");
        registerForm.classList.remove("hidden");
        animateSwitch(registerForm);
      });

      const activeTab = document.body.dataset.activeTab;
      if (activeTab === "register") {
        tabRegister.click();
      }

      function animateSwitch(element) {
        element.classList.add("fade-slide-enter");
        setTimeout(() => {
          element.classList.remove("fade-slide-enter");
        }, 100);
      }
    </script>
  </body>
</html>
