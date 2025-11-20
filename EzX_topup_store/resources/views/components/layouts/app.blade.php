<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EzX Store</title>
  {{-- <script>
    (() => {
      try {
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme === 'dark') {
          document.documentElement.classList.add('dark');
        }
        // Hapus bagian matchMedia (prefers-color-scheme)
      } catch (_) { /* ignore */ }
    })();

  </script> --}}
  @vite('resources/css/app.css')

  <style>
    html {
      scroll-behavior: smooth;
    }

  </style>
</head>

<body class="flex min-h-screen flex-col bg-(--bg1) text-gray-800 transition-colors duration-500">

  {{-- Navbar --}}
  {{-- <header class="sticky top-0 z-50 backdrop-blur-md">
    @include('components.layout.navbar')
  </header> --}}
 <x-navbar />
 

    <div class="flex-1">

    @hasSection('carousel')
    <div>
        @yield('carousel')
    </div>
     @endif


      @hasSection('content')
        <main class="">
          <div class="animate-fade-up">
            @yield('content')
          </div>
        </main>
      @endif


      @hasSection('list_all_games')
        <main class="" >
          <div>
            @yield('list_all_games')
          </div>
        </main>
      @endif
      
    </div>

    @hasSection('sidebar-admin')
    <div>
        @yield('sidebar-admin')
    </div>
     @endif
     
    @hasSection('sidebar-superadmin')
    <div>
        @yield('sidebar-superadmin')
    </div>
     @endif

    @hasSection('sidebar-user')
    <div>
        @yield('sidebar-user')
    </div>
     @endif

  
    <x-fab/>
    {{-- Footer --}}
    {{-- @include('components.layout.footer') --}}
    <x-footer />

  @if (session('auth_success'))
    <div id="auth-success-toast" class="toast toast-end z-[60]">
      <div class="alert alert-success shadow-lg flex items-start gap-3">
        <span>{{ session('auth_success') }}</span>
        <button type="button" class="btn btn-ghost btn-xs" data-toast-close>&times;</button>
      </div>
    </div>
    <script>
      (() => {
        const toast = document.getElementById('auth-success-toast');
        if (!toast) {
          return;
        }
        const removeToast = () => toast.remove();
        const closeButton = toast.querySelector('[data-toast-close]');
        if (closeButton) {
          closeButton.addEventListener('click', removeToast);
        }
        setTimeout(removeToast, 5000);
      })();
    </script>
  @endif


</body>

</html>