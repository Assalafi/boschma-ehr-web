document.addEventListener("DOMContentLoaded", function () {
    var activeTab = "triaged",
        currentPage = 1,
        debounceTimer;

    // Tab switching
    document.querySelectorAll(".queue-tab").forEach(function (btn) {
        btn.addEventListener("click", function () {
            document.querySelectorAll(".queue-tab").forEach(function (b) {
                b.classList.remove("active");
            });
            this.classList.add("active");
            activeTab = this.getAttribute("data-tab");
            currentPage = 1;
            // Show/hide toolbars
            document.querySelectorAll(".tab-toolbar").forEach(function (t) {
                t.style.display = "none";
            });
            var tb = document.querySelector(
                '[data-toolbar="' + activeTab + '"]',
            );
            if (tb) tb.style.display = "flex";
            loadTab();
        });
    });

    // Filter changes
    document.querySelectorAll(".tab-filter").forEach(function (el) {
        el.addEventListener("change", function () {
            currentPage = 1;
            loadTab();
        });
    });

    // Search inputs (debounced)
    document.querySelectorAll(".tab-search-input").forEach(function (el) {
        el.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                currentPage = 1;
                loadTab();
            }, 400);
        });
    });

    function getFilters() {
        var toolbar = document.querySelector(
            '[data-toolbar="' + activeTab + '"]',
        );
        if (!toolbar) return {};
        var params = { tab: activeTab, page: currentPage };
        toolbar.querySelectorAll("[data-filter]").forEach(function (el) {
            var key = el.getAttribute("data-filter");
            var val = el.value.trim();
            if (val) params[key] = val;
        });
        return params;
    }

    function loadTab() {
        var body = document.getElementById("tabBody");
        var pgn = document.getElementById("tabPagination");
        body.classList.add("loading");
        body.innerHTML =
            body.innerHTML ||
            '<div style="padding:80px;text-align:center;color:#94a3b8">Loading...</div>';
        var params = getFilters();
        var qs = Object.keys(params)
            .map(function (k) {
                return k + "=" + encodeURIComponent(params[k]);
            })
            .join("&");
        fetch(QUEUE_URL + "?" + qs, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then(function (r) {
                if (!r.ok) throw new Error("Server error " + r.status);
                return r.json();
            })
            .then(function (data) {
                body.classList.remove("loading");
                if (!data.html) {
                    body.innerHTML =
                        '<div style="padding:60px;text-align:center;color:#dc2626">No data returned</div>';
                    return;
                }
                body.innerHTML = data.html;
                // Update badge count
                var badge = document.querySelector(
                    '[data-count="' + activeTab + '"]',
                );
                if (badge) badge.textContent = data.count;
                // Update info
                var info = document.querySelector(
                    '[data-info="' + activeTab + '"]',
                );
                if (info) {
                    var pp = parseInt(params.per_page) || 15;
                    var from = (data.page - 1) * pp + 1,
                        to = Math.min(data.page * pp, data.count);
                    info.textContent =
                        data.count > 0
                            ? from + "-" + to + " of " + data.count
                            : "0 records";
                }
                // Pagination
                renderPagination(pgn, data.page, data.pages, data.count);
            })
            .catch(function (e) {
                body.classList.remove("loading");
                body.innerHTML =
                    '<div style="padding:60px;text-align:center;color:#dc2626"><span class="material-symbols-outlined" style="font-size:40px;display:block;margin:0 auto 8px">error</span>Failed to load data. Please try again.</div>';
            });
    }

    function renderPagination(el, page, pages, total) {
        if (!el || pages <= 1) {
            if (el) el.innerHTML = "";
            return;
        }
        var h = "";
        h +=
            '<button class="tab-page-btn"' +
            (page <= 1 ? " disabled" : "") +
            ' data-p="' +
            (page - 1) +
            '">&#8249;</button>';
        var pgs = [];
        if (pages <= 7) {
            for (var i = 1; i <= pages; i++) pgs.push(i);
        } else {
            pgs = [1];
            if (page > 3) pgs.push("...");
            for (
                var i = Math.max(2, page - 1);
                i <= Math.min(pages - 1, page + 1);
                i++
            )
                pgs.push(i);
            if (page < pages - 2) pgs.push("...");
            pgs.push(pages);
        }
        pgs.forEach(function (p) {
            if (p === "...")
                h +=
                    '<span style="padding:0 6px;color:#94a3b8">&hellip;</span>';
            else
                h +=
                    '<button class="tab-page-btn' +
                    (p === page ? " active" : "") +
                    '" data-p="' +
                    p +
                    '">' +
                    p +
                    "</button>";
        });
        h +=
            '<button class="tab-page-btn"' +
            (page >= pages ? " disabled" : "") +
            ' data-p="' +
            (page + 1) +
            '">&#8250;</button>';
        el.innerHTML = h;
        el.querySelectorAll(".tab-page-btn:not([disabled])").forEach(
            function (b) {
                b.addEventListener("click", function () {
                    currentPage = parseInt(this.getAttribute("data-p"));
                    loadTab();
                    document
                        .querySelector(".doc-card")
                        .scrollIntoView({ behavior: "smooth", block: "start" });
                });
            },
        );
    }

    // Initial load
    loadTab();
});
