const API_URL = 'api.php';
const LANG = 'ar';

async function fetchAPI(action, params = {}) {
    try {
        const queryParams = new URLSearchParams({ action, lang: LANG, ...params });
        const response = await fetch(`${API_URL}?${queryParams}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('خطأ في الاتصال:', error);
        return { error: true, message: 'حدث خطأ في الاتصال' };
    }
}

function createCard(item) {
    const id = item.id || item.title_id;
    const title = item.title || item.name || 'بدون عنوان';
    const poster = item.poster || item.image || 'assets/images/placeholder.jpg';
    const rating = item.rating || item.imdb_rating || '0';
    const description = item.description || item.synopsis || '';

    return `
        <div class="card" onclick="openTitle(${id})">
            <div style="position: relative;">
                <img src="${poster}" alt="${title}" class="card-image" onerror="this.src='assets/images/placeholder.jpg'">
                <div class="play-button">
                    <i class="fas fa-play"></i>
                </div>
            </div>
            <div class="card-content">
                <div class="card-title">${title}</div>
                <div class="card-description">${description}</div>
                <div class="card-rating">
                    <span class="stars">
                        <i class="fas fa-star"></i> ${rating}
                    </span>
                </div>
            </div>
        </div>
    `;
}

async function loadHome() {
    const featured = document.getElementById('featuredContainer');
    const latest = document.getElementById('latestContainer');

    const homeData = await fetchAPI('home');
    if (!homeData.error && homeData.data && homeData.data.featured) {
        featured.innerHTML = homeData.data.featured
            .slice(0, 12)
            .map(item => createCard(item))
            .join('');
    } else {
        featured.innerHTML = '<p>فشل تحميل المحتوى</p>';
    }

    const browseData = await fetchAPI('browse', { limit: 12 });
    if (!browseData.error && browseData.data) {
        const items = Array.isArray(browseData.data) ? browseData.data : browseData.data.results || [];
        latest.innerHTML = items
            .slice(0, 12)
            .map(item => createCard(item))
            .join('');
    } else {
        latest.innerHTML = '<p>فشل تحميل المحتوى</p>';
    }
}

async function search(query) {
    if (!query.trim()) {
        alert('أدخل نص البحث');
        return;
    }

    const searchSection = document.getElementById('searchSection');
    const resultsContainer = document.getElementById('searchResultsContainer');
    const featuredContainer = document.getElementById('featuredContainer').parentElement;
    const latestContainer = document.getElementById('latestContainer').parentElement;

    searchSection.style.display = 'block';
    featuredContainer.style.display = 'none';
    latestContainer.style.display = 'none';

    resultsContainer.innerHTML = '<div class="loading"><div class="spinner"></div>جاري البحث...</div>';

    const results = await fetchAPI('search', { q: query });
    if (!results.error && results.data) {
        const items = Array.isArray(results.data) ? results.data : results.data.results || [];
        if (items.length > 0) {
            resultsContainer.innerHTML = items
                .map(item => createCard(item))
                .join('');
        } else {
            resultsContainer.innerHTML = '<p style="text-align: center; padding: 40px;">لم يتم العثور على نتائج</p>';
        }
    } else {
        resultsContainer.innerHTML = '<p style="text-align: center; padding: 40px;">حدث خطأ في البحث</p>';
    }
}

async function openTitle(titleId) {
    const modal = document.getElementById('videoModal');
    const videoInfo = document.getElementById('videoInfo');
    const videoPlayer = document.getElementById('videoPlayer');

    videoInfo.innerHTML = '<div class="loading"><div class="spinner"></div>جاري التحميل...</div>';
    videoPlayer.innerHTML = '';
    modal.style.display = 'block';

    const titleData = await fetchAPI('title', { id: titleId });

    if (!titleData.error && titleData.data) {
        const title = titleData.data;
        let html = `
            <h2>${title.title || title.name}</h2>
            <p><strong>السنة:</strong> ${title.year || 'غير محدد'}</p>
            <p><strong>التقييم:</strong> ${title.rating || title.imdb_rating || 'غير محدد'}</p>
            <p><strong>الوصف:</strong> ${title.description || title.synopsis || 'لا يوجد وصف'}</p>
        `;

        if (title.type === 'series') {
            html += `<button onclick="loadEpisodes(${titleId})" class="btn-primary" style="margin-top: 20px; padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer;">مشاهدة الحلقات</button>`;
        } else {
            html += `<button onclick="loadPlay(${titleId})" class="btn-primary" style="margin-top: 20px; padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer;">مشاهدة الآن</button>`;
        }

        videoInfo.innerHTML = html;
    } else {
        videoInfo.innerHTML = '<p>فشل تحميل البيانات</p>';
    }
}

async function loadEpisodes(titleId) {
    const videoInfo = document.getElementById('videoInfo');
    videoInfo.innerHTML = '<div class="loading"><div class="spinner"></div>جاري تحميل الحلقات...</div>';

    const episodesData = await fetchAPI('episodes', { id: titleId });

    if (!episodesData.error && episodesData.data) {
        const episodes = Array.isArray(episodesData.data) ? episodesData.data : episodesData.data.results || [];
        let html = '<h3>الحلقات</h3><div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">';

        episodes.forEach(ep => {
            html += `
                <button onclick="playEpisode('${ep.id}')" 
                    style="padding: 10px; background: var(--card-bg); border: 1px solid var(--primary-color); color: white; border-radius: 5px; cursor: pointer;">
                    الحلقة ${ep.episode_number || ep.number}
                </button>
            `;
        });

        html += '</div>';
        videoInfo.innerHTML = html;
    } else {
        videoInfo.innerHTML = '<p>فشل تحميل الحلقات</p>';
    }
}

async function playEpisode(episodeId) {
    const videoPlayer = document.getElementById('videoPlayer');
    videoPlayer.innerHTML = '<div class="loading"><div class="spinner"></div>جاري التحميل...</div>';

    const playData = await fetchAPI('play', { eid: episodeId, with_sub: 1 });

    if (!playData.error && playData.data) {
        const video = playData.data;
        if (video.url) {
            videoPlayer.innerHTML = `
                <iframe src="${video.url}" 
                    allowfullscreen 
                    allow="autoplay" 
                    frameborder="0">
                </iframe>
            `;
        } else if (video.embed_url) {
            videoPlayer.innerHTML = `
                <iframe src="${video.embed_url}" 
                    allowfullscreen 
                    allow="autoplay" 
                    frameborder="0">
                </iframe>
            `;
        } else {
            videoPlayer.innerHTML = '<p>لم يتم العثور على رابط التشغيل</p>';
        }
    } else {
        videoPlayer.innerHTML = '<p>فشل تحميل الفيديو</p>';
    }
}

function closeModal() {
    document.getElementById('videoModal').style.display = 'none';
}

async function loadCategory() {
    alert('تحميل الفئات قيد التطوير');
}

async function loadTrending() {
    alert('تحميل الأكثر مشاهدة قيد التطوير');
}

document.addEventListener('DOMContentLoaded', () => {
    loadHome();

    document.getElementById('searchBtn').addEventListener('click', () => {
        const query = document.getElementById('searchInput').value;
        search(query);
    });

    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const query = document.getElementById('searchInput').value;
            search(query);
        }
    });

    window.addEventListener('click', (e) => {
        const modal = document.getElementById('videoModal');
        if (e.target === modal) {
            closeModal();
        }
    });
});