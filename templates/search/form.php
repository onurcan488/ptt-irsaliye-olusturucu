<div class="row mb-5">
    <div class="col-md-8 mx-auto">
        <div class="row align-items-center mb-4">
            <div class="col-md-12 text-center">
                <h2 class="mb-0 fw-bold"><i class="fas fa-search me-2 text-info"></i>Gönderi Sorgula</h2>
                <p class="text-muted mb-0">Barkod numarası, evrak no veya adres ile detaylı arama yapın.</p>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="searchForm" onsubmit="event.preventDefault(); search();">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchTerm" class="form-control border-start-0 ps-0"
                            placeholder="Barkod No, Evrak No veya Adres giriniz..." autofocus>
                        <button class="btn btn-primary px-4 fw-bold" type="submit">SORGULA</button>
                    </div>
                    <div class="form-text mt-2 text-muted text-center">En az 3 karakter giriniz.</div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div id="resultsArea"></div>
        <div id="paginationArea" class="mt-4"></div>
    </div>
</div>