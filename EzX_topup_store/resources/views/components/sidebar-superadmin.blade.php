<div class="drawer drawer-open">
  <input id="my-drawer-4" type="checkbox" class="drawer-toggle" />
  <div class="drawer-content p-4">
    <!-- ========== PAGE CONTENT ========== -->
    <div id="superadmin-content" class="space-y-10">
      {{ $slot ?? '' }}
    </div>
  </div>

  <div class="drawer-side is-drawer-close:overflow-visible">
    <label for="my-drawer-4" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="is-drawer-close:w-14 is-drawer-open:w-64 bg-(--bg2) flex flex-col items-start min-h-full">

      <!-- SIDEBAR MENU -->
      <ul class="menu w-full grow text-(--textsub1)">

        <!-- HOMEPAGE -->
        <li>
          <button data-sidebar-target="dashboard" class="btn btn-ghost justify-start gap-3 w-full is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Dashboard" onclick="loadSuperPage('dashboard')">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block size-4 my-1.5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path d="M3 13h8V3H3v10zM3 21h8v-6H3v6zm10 0h8v-10h-8v10zm0-18v6h8V3h-8z" />
            </svg>
            <span class="is-drawer-close:hidden">Dashboard</span>
          </button>
        </li>

        <!-- MANAJEMEN AKUN -->
        <li>
          <button data-sidebar-target="accounts" class="btn btn-ghost justify-start gap-3 w-full is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Manajemen Akun" onclick="loadSuperPage('accounts')">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block size-4 my-1.5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path d="M12 2l9 4.9v9.8L12 22l-9-5.3V6.9L12 2z" />
            </svg>
            <span class="is-drawer-close:hidden">Manajemen Akun</span>
          </button>
        </li>

        <!-- FORM PERSETUJUAN -->
        <li>
          <button data-sidebar-target="reports" class="btn btn-ghost justify-start gap-3 w-full is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Laporan Keuangan" onclick="loadSuperPage('reports')">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block size-4 my-1.5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
            <span class="is-drawer-close:hidden">Laporan Keuangan</span>
          </button>
        </li>


      </ul>

        @auth
        <div class="w-full p-4 border-t border-base-200">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-error btn-outline justify-start gap-3 w-full is-drawer-close:tooltip is-drawer-close:tooltip-right" type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-4 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4" />
                <path d="M10 17l5-5-5-5" />
                <path d="M15 12H3" />
              </svg>
               <span class="is-drawer-close:hidden">Sign Out</span>
            </button>
          </form>
        </div>
      @endauth

      <!-- TOGGLE BUTTON -->
      <div class="m-2 is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Open">
        <label for="my-drawer-4" class="btn btn-ghost btn-circle drawer-button is-drawer-open:rotate-y-180">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" fill="none" stroke="currentColor"
            class="inline-block size-4 my-1.5">
            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
            <path d="M9 4v16"></path>
            <path d="M14 10l2 2l-2 2"></path>
          </svg>
        </label>
      </div>

    </div>
  </div>

@once
  <script>
    window.loadSuperPage = function (page) {
      const sections = document.querySelectorAll('#superadmin-content [data-page]');
      const buttons = document.querySelectorAll('[data-sidebar-target]');

      sections.forEach((section) => section.classList.add('hidden'));
      buttons.forEach((button) => button.classList.remove('btn-active'));

      let targetSection = document.querySelector(`#superadmin-content [data-page="${page}"]`);
      if (!targetSection && sections.length > 0) {
        targetSection = sections[0];
        page = targetSection.dataset.page;
      }

      if (targetSection) {
        targetSection.classList.remove('hidden');
        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      const activeButton = document.querySelector(`[data-sidebar-target="${page}"]`);
      if (activeButton) {
        activeButton.classList.add('btn-active');
      }

      if (page) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', page);
        url.hash = page;
        history.replaceState(null, '', url.toString());
      }
    };

    document.addEventListener('DOMContentLoaded', () => {
      const url = new URL(window.location.href);
      const initialPage = url.searchParams.get('tab') || (window.location.hash ? window.location.hash.substring(1) : 'dashboard');
      loadSuperPage(initialPage);
    });
  </script>
@endonce
</div>
