{{-- Modal search ala Laravel docs: overlay + live grouped result --}}
<div id="search-modal" class="fixed inset-0 z-50 hidden">
    <div id="search-modal-backdrop" class="absolute inset-0 bg-gray-900/40"></div>

    <div class="relative mx-auto mt-20 sm:mt-28 w-full max-w-xl px-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    id="search-modal-input"
                    type="text"
                    placeholder="Cari nama pencipta, ringkasan, atau isi dokumen..."
                    autocomplete="off"
                    class="flex-1 text-sm outline-none placeholder-gray-400"
                >
                <button id="search-modal-close" type="button" class="text-gray-400 hover:text-gray-600 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="search-modal-results" class="max-h-96 overflow-y-auto p-2 text-sm">
                <p class="text-center text-gray-400 py-10 text-sm">Ketik minimal 2 huruf untuk mulai mencari...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('search-modal');
    const backdrop = document.getElementById('search-modal-backdrop');
    const input = document.getElementById('search-modal-input');
    const closeBtn = document.getElementById('search-modal-close');
    const resultsBox = document.getElementById('search-modal-results');
    const trigger = document.getElementById('search-trigger');

    let debounceTimer = null;

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => input.focus(), 10);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderResults(data) {
        const hasMetadata = data.metadata && data.metadata.length > 0;
        const hasContent = data.content && data.content.length > 0;

        if (!hasMetadata && !hasContent) {
            resultsBox.innerHTML = '<p class="text-center text-gray-400 py-10 text-sm">Tidak ada hasil.</p>';
            return;
        }

        let html = '';

        if (hasMetadata) {
            html += '<div class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Arsip</div>';
            data.metadata.forEach((item) => {
                html += `
                    <a href="/?q=${encodeURIComponent(item.title)}#arsip-row-${item.id}"
                       class="block rounded-lg px-3 py-2 hover:bg-gray-50">
                        <div class="text-gray-800 font-medium">${escapeHtml(item.title)}</div>
                        ${item.subtitle ? `<div class="text-gray-400 text-xs mt-0.5">${escapeHtml(item.subtitle)}</div>` : ''}
                    </a>`;
            });
        }

        if (hasContent) {
            html += '<div class="px-3 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Isi Dokumen</div>';
            data.content.forEach((item) => {
                html += `
                    <a href="/documents/${item.document_id}/view#page=${item.page_number}" target="_blank"
                       class="block rounded-lg px-3 py-2 hover:bg-gray-50">
                        <div class="text-gray-800 font-medium">${escapeHtml(item.arsip_title)} <span class="text-gray-400 font-normal text-xs">— hal. ${item.page_number}</span></div>
                        <div class="text-gray-400 text-xs mt-0.5">...${escapeHtml(item.snippet)}...</div>
                    </a>`;
            });
        }

        resultsBox.innerHTML = html;
    }

    function doSearch(q) {
        if (q.trim().length < 2) {
            resultsBox.innerHTML = '<p class="text-center text-gray-400 py-10 text-sm">Ketik minimal 2 huruf untuk mulai mencari...</p>';
            return;
        }
        fetch(`{{ route('search.live') }}?q=${encodeURIComponent(q)}`)
            .then((res) => res.json())
            .then(renderResults)
            .catch(() => {
                resultsBox.innerHTML = '<p class="text-center text-red-400 py-10 text-sm">Gagal memuat hasil pencarian.</p>';
            });
    }

    input.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const value = e.target.value;
        debounceTimer = setTimeout(() => doSearch(value), 300);
    });

    if (trigger) {
        trigger.addEventListener('click', openModal);
    }
    backdrop.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const cmdOrCtrl = isMac ? e.metaKey : e.ctrlKey;

        if (cmdOrCtrl && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openModal();
        }
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
