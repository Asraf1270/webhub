document.addEventListener('DOMContentLoaded', () => {
    const siteCards   = document.getElementById('site-cards');
    const emptyState  = document.getElementById('empty-state');
    const searchInput = document.getElementById('searchInput');
    const sortSelect  = document.getElementById('sortSelect');
    const modeToggle  = document.getElementById('modeToggle');

    let sites = [];

    fetch('data/websites.json')
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            sites = data;
            render();
        })
        .catch(() => {
            siteCards.innerHTML = '<div class="text-center py-5"><p class="text-danger">Failed to load websites.</p></div>';
        });

    const render = () => {
        const filtered = search(sites);
        const sorted = sort(filtered);
        siteCards.innerHTML = '';
        if (!sorted.length) {
            emptyState.classList.remove('d-none');
            return;
        }
        emptyState.classList.add('d-none');
        sorted.forEach(site => siteCards.appendChild(createCard(site)));
        addVisitListeners();
    };

    const createCard = site => {
        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
            <img src="${site.thumbnail}" alt="${site.title}" onerror="this.src='https://via.placeholder.com/300x180/1a1a2e/ffffff?text=No+Image'">
            <div class="card-content">
                <h3>${escape(site.title)}</h3>
                <p>${escape(site.description)}</p>
                <p><strong>Category:</strong> ${escape(site.category)}</p>
                <a href="#" class="visit-btn" data-url="${site.url}" data-id="${site.id}">
                    Launch <span class="view-count">(${site.views} ${site.views === 1 ? 'view' : 'views'})</span>
                </a>
            </div>
        `;
        return card;
    };

    const escape = t => {
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    };

    const addVisitListeners = () => {
        document.querySelectorAll('.visit-btn').forEach(btn => {
            btn.onclick = e => {
                e.preventDefault();
                const id = btn.dataset.id;
                const url = btn.dataset.url;

                fetch('increment_view.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const s = sites.find(x => x.id == id);
                        if (s) {
                            s.views++;
                            btn.querySelector('.view-count').textContent = `(${s.views} ${s.views === 1 ? 'view' : 'views'})`;
                            if (sortSelect.value === 'most-viewed') render();
                        }
                    }
                    window.open(url, '_blank');
                })
                .catch(() => window.open(url, '_blank'));
            };
        });
    };

    const search = data => {
        const q = searchInput.value.trim().toLowerCase();
        return q ? data.filter(s => 
            s.title.toLowerCase().includes(q) || 
            s.category.toLowerCase().includes(q)
        ) : data;
    };
    searchInput.addEventListener('input', render);

    const sort = data => {
        const by = sortSelect.value;
        const arr = [...data];
        if (by === 'newest') arr.sort((a,b) => new Date(b.date_added) - new Date(a.date_added));
        else if (by === 'alphabetical') arr.sort((a,b) => a.title.localeCompare(b.title));
        else if (by === 'most-viewed') arr.sort((a,b) => b.views - a.views);
        return arr;
    };
    sortSelect.addEventListener('change', render);

    modeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        modeToggle.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (saved === 'dark' || (!saved && prefersDark)) {
        document.body.classList.add('dark-mode');
        modeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }
});