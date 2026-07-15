        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const root = document.documentElement;
  const openButton = document.querySelector('[data-admin-sidebar-open]');
  const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');

  if (openButton) {
    openButton.addEventListener('click', function () {
      root.classList.add('admin-sidebar-open');
    });
  }

  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      root.classList.remove('admin-sidebar-open');
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      root.classList.remove('admin-sidebar-open');
    }
  });
});
</script>
