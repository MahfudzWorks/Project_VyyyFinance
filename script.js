document.addEventListener('DOMContentLoaded', () => {

    // 1. EFEK LOADING OVERLAY SEMENTARA HALAMAN DISUBMIT / DIPINDAH
    const loaderOverlay = document.getElementById('loader-overlay');

    function showLoader() {
        if (loaderOverlay) {
            loaderOverlay.classList.remove('hidden');
            loaderOverlay.classList.add('flex');
        }
    }

    function hideLoader() {
        if (loaderOverlay) {
            loaderOverlay.classList.add('hidden');
            loaderOverlay.classList.remove('flex');
        }
    }

    // Sembunyikan loader saat halaman selesai dimuat
    hideLoader();

    // Tampilkan loader ketika menavigasi atau submit form
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            showLoader();
        });
    });

    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            // Jangan trigger loader jika hanya link anchor internal atau aksi konfirmasi yang dibatalkan
            if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                showLoader();
            }
        });
    });

    // 2. ANIMASI ANGKA STATISTIK (Counter Animation)
    const counters = document.querySelectorAll('.counter-anim');
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target')) || 0;
        const duration = 1000; // 1 detik
        const stepTime = 20;
        const steps = duration / stepTime;
        const increment = target / steps;
        let current = 0;

        if (target === 0) {
            counter.innerText = 'Rp 0';
            return;
        }

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
                current = target;
                clearInterval(timer);
            }
            counter.innerText = 'Rp ' + Math.round(current).toLocaleString('id-ID');
        }, stepTime);
    });

    // 3. EFEK INTERAKTIF INPUT & TOOLTIP MODAL
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            // Efek subtle visual saat mengetik pencarian
            if (e.target.value.length > 0) {
                e.target.classList.add('border-indigo-500', 'bg-indigo-50/20');
            } else {
                e.target.classList.remove('border-indigo-500', 'bg-indigo-50/20');
            }
        });
    }

});

// 4. KONFIRMASI HAPUS MODERN (MODAL CUSTOM)
let deleteTargetUrl = '';

function confirmDelete(url, itemTitle) {
    deleteTargetUrl = url;
    const modal = document.getElementById('delete-modal');
    const modalItemText = document.getElementById('modal-item-name');
    
    if (modalItemText) {
        modalItemText.innerText = itemTitle || 'transaksi ini';
    }
    
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    return false;
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    deleteTargetUrl = '';
}

function proceedDelete() {
    if (deleteTargetUrl) {
        const loaderOverlay = document.getElementById('loader-overlay');
        if (loaderOverlay) {
            loaderOverlay.classList.remove('hidden');
            loaderOverlay.classList.add('flex');
        }
        window.location.href = deleteTargetUrl;
    }
}