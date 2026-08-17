document.addEventListener('DOMContentLoaded', () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;

  document.querySelectorAll('[data-connection-action]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const url = form.getAttribute('action');
      const method = form.dataset.connectionMethod || 'POST';
      const confirmMessage = form.dataset.connectionConfirm;

      if (confirmMessage && !window.confirm(confirmMessage)) {
        return;
      }

      try {
        const response = await fetch(url, {
          method,
          headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: new FormData(form),
        });

        if (!response.ok) {
          throw new Error('Request failed');
        }

        showToast('Connections updated.', 'success');
        window.location.reload();
      } catch (error) {
        showToast('Could not update connections. Please try again.', 'error');
      }
    });
  });

  const copyBtn = document.getElementById('conn-copy-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async () => {
      const code = copyBtn.dataset.copyCode;
      if (!code) return;

      try {
        await navigator.clipboard.writeText(code);
        showToast('Connection code copied.', 'success');
      } catch (error) {
        showToast('Could not copy the code. Please copy it manually.', 'error');
      }
    });
  }
});