document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;               // button that triggered
        const siteId = button.getAttribute('data-site-id');
        const siteTitle = button.getAttribute('data-site-title');

        // Fill modal
        modal.querySelector('#modalSiteTitle').textContent = siteTitle;
        modal.querySelector('#modalSiteId').value = siteId;
    });
});