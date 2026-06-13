/**
 * Item page - favorites and report
 */
document.addEventListener('DOMContentLoaded', () => {
    initFavoriteButtons();
    initReportForm();
});

function initFavoriteButtons() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const productId = btn.dataset.productId;
            if (!productId) return;

            try {
                const res = await fetch(`${getSiteUrl()}/api/toggle-favorite.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId }),
                });
                const data = await res.json();

                if (data.success) {
                    btn.classList.toggle('active', data.favorited);
                    btn.innerHTML = `<i class="fas fa-heart"></i> ${data.favorited ? 'Saved' : 'Save'}`;
                    showToast(data.message);
                } else {
                    if (res.status === 401) window.location.href = `${getSiteUrl()}/auth/login.php`;
                    else showToast(data.message, 'error');
                }
            } catch {
                showToast('Something went wrong', 'error');
            }
        });
    });
}

function initReportForm() {
    const form = document.getElementById('reportForm');
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const reason = formData.get('reason');
        const productId = formData.get('product_id');

        try {
            const res = await fetch(`${getSiteUrl()}/api/report.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, reason }),
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('reportModal')?.classList.remove('show');
                form.reset();
                showToast(data.message);
            } else {
                showToast(data.message, 'error');
            }
        } catch {
            showToast('Failed to submit report', 'error');
        }
    });
}

function getSiteUrl() {
    return document.querySelector('link[href*="style.css"]')?.href.replace('/assets/css/style.css', '') || '';
}
