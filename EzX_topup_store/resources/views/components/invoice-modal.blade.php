<dialog id="invoiceModal" class="modal">
    <div class="modal-box max-w-4xl space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-(--text1)">Pratinjau Struk</h3>
                <p class="text-sm text-(--textsub1)">Gunakan tombol di bawah untuk menyimpan sebagai PDF atau gambar.
                </p>
            </div>
            <form method="dialog">
                <button class="btn btn-sm btn-circle">✕</button>
            </form>
        </div>

        <div id="invoiceActions" class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-primary btn-sm" data-invoice-action="print">Simpan / Cetak PDF</button>
            {{-- <button type="button" class="btn btn-outline btn-sm" data-invoice-action="image">Simpan sebagai
                Gambar</button> --}}
        </div>

        <div id="invoiceContainer"
            class="max-h-[70vh] overflow-auto rounded-2xl border border-base-300 bg-base-100 p-4">
            <div class="text-center text-sm text-(--textsub1)">Pilih transaksi yang telah selesai untuk melihat struk.
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>Tutup</button>
    </form>
</dialog>

@once
    <script>
        (function () {
            const modal = document.getElementById('invoiceModal');
            const container = document.getElementById('invoiceContainer');
            const actionRoot = document.getElementById('invoiceActions');
            let currentUrl = null;
            let isLoading = false;

            async function ensureHtml2canvas() {
                if (window.html2canvas) {
                    return;
                }

                await new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-html2canvas]');
                    if (existing) {
                        existing.addEventListener('load', resolve, { once: true });
                        existing.addEventListener('error', reject, { once: true });
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                    script.crossOrigin = 'anonymous';
                    script.setAttribute('data-html2canvas', 'true');
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            async function waitForImages(root) {
                const images = Array.from(root.querySelectorAll('img'));
                if (!images.length) {
                    return;
                }

                await Promise.all(
                    images.map((img) => {
                        if (img.complete && img.naturalHeight !== 0) {
                            return Promise.resolve();
                        }
                        return new Promise((resolve, reject) => {
                            img.addEventListener('load', resolve, { once: true });
                            img.addEventListener('error', () => {
                                resolve();
                            }, { once: true });
                        });
                    })
                );
            }

            function setLoading(state) {
                isLoading = state;
                if (state) {
                    container.innerHTML = '<div class="flex min-h-[200px] items-center justify-center text-sm text-(--textsub1)">Memuat struk...</div>';
                }
            }

            async function openInvoice(url) {
                if (!modal || !container || !url) {
                    return;
                }
                currentUrl = url;
                setLoading(true);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memuat struk');
                    }

                    const html = await response.text();
                    container.innerHTML = html;
                    const invoiceRoot = container.querySelector('#invoiceRoot');
                    if (invoiceRoot) {
                        await waitForImages(invoiceRoot);
                    }

                    if (typeof modal.showModal === 'function' && !modal.open) {
                        modal.showModal();
                    }
                } catch (error) {
                    console.error(error);
                    container.innerHTML = '<div class="alert alert-error">Tidak dapat memuat struk. Coba lagi nanti.</div>';
                    currentUrl = null;
                } finally {
                    setLoading(false);
                }
            }

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-invoice-url]');
                if (trigger) {
                    event.preventDefault();
                    const url = trigger.getAttribute('data-invoice-url');
                    if (url && !isLoading) {
                        openInvoice(url);
                    }
                }
            });

            if (actionRoot) {
                actionRoot.addEventListener('click', async (event) => {
                    const action = event.target.getAttribute('data-invoice-action');
                    if (!action || !currentUrl) {
                        return;
                    }

                    if (action === 'print') {
                        window.open(currentUrl + '?mode=print', '_blank', 'noopener');
                        return;
                    }

                    if (action === 'image') {
                        await ensureHtml2canvas();
                        const invoiceRoot = container.querySelector('#invoiceRoot');
                        if (!invoiceRoot) {
                            alert("Struk tidak ditemukan.");
                            return;
                        }

                        // Pastikan ukuran fix agar html2canvas tidak kacau
                        invoiceRoot.style.width = "320px";
                        invoiceRoot.style.maxWidth = "320px";
                        invoiceRoot.style.background = "#ffffff";
                        invoiceRoot.style.padding = "0";
                        invoiceRoot.style.margin = "0 auto";


                        await waitForImages(invoiceRoot);

                        const canvas = await window.html2canvas(invoiceRoot, {
                            backgroundColor: '#ffffff',
                            scale: 3,
                            useCORS: true,
                            allowTaint: true,
                            imageTimeout: 0,

                        });

                        const link = document.createElement('a');
                        link.download = `${(invoiceRoot.dataset.invoiceNumber || 'invoice')}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }
                });
            }
        })();
    </script>
@endonce