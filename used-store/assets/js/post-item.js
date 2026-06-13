/**
 * Post item - image upload preview
 */
document.addEventListener('DOMContentLoaded', () => {
    const area = document.getElementById('imageUploadArea');
    const input = document.getElementById('images');
    const preview = document.getElementById('imagePreview');
    const maxFiles = 5;

    if (!area || !input) return;

    ['dragenter', 'dragover'].forEach(evt => {
        area.addEventListener(evt, (e) => { e.preventDefault(); area.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        area.addEventListener(evt, (e) => { e.preventDefault(); area.classList.remove('dragover'); });
    });

    area.addEventListener('drop', (e) => {
        input.files = e.dataTransfer.files;
        showPreview(input.files);
    });

    input.addEventListener('change', () => showPreview(input.files));

    function showPreview(files) {
        preview.innerHTML = '';
        const count = Math.min(files.length, maxFiles);

        if (files.length > maxFiles) {
            showToast(`Maximum ${maxFiles} images allowed`, 'error');
        }

        for (let i = 0; i < count; i++) {
            const file = files[i];
            if (!file.type.startsWith('image/')) continue;

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }

    document.getElementById('postForm')?.addEventListener('submit', (e) => {
        const title = document.getElementById('title').value.trim();
        const desc = document.getElementById('description').value.trim();
        const price = document.getElementById('price').value;

        if (title.length < 3) { e.preventDefault(); showToast('Title must be at least 3 characters', 'error'); }
        else if (desc.length < 10) { e.preventDefault(); showToast('Description must be at least 10 characters', 'error'); }
        else if (price <= 0) { e.preventDefault(); showToast('Please enter a valid price', 'error'); }
    });
});
