{{-- Placeholder yang menutupi konten selama halaman masih dimuat.

     Sengaja TIDAK tampil di HTML awal: pages/partials/page-skeleton.js
     memunculkannya hanya bila halaman belum selesai dalam ambang waktu,
     sehingga respons cepat tidak pernah berkedip skeleton. --}}
<div class="page-skeleton" data-page-skeleton hidden aria-hidden="true">
    <div class="page-skeleton__inner">
        <span class="skeleton skeleton--text w-25 mb-3"></span>
        <span class="skeleton skeleton--value w-50 mb-4"></span>

        <div class="page-skeleton__grid">
            @for ($i = 0; $i < 4; $i++)
                <div class="page-skeleton__cell"></div>
            @endfor
        </div>

        <div class="page-skeleton__rows">
            @for ($i = 0; $i < 3; $i++)
                <div class="d-flex align-items-center gap-3">
                    <span class="skeleton skeleton--avatar"></span>
                    <div class="flex-grow-1">
                        <span class="skeleton skeleton--text w-50"></span>
                        <span class="skeleton skeleton--text w-75 mt-2"></span>
                    </div>
                    <span class="skeleton skeleton--badge"></span>
                </div>
            @endfor
        </div>
    </div>
</div>