<?php
/**
 * موقع الدراما - الصفحة الرئيسية
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>موقع الدراما - مشاهدة أفضل المسلسلات والأفلام</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-tv"></i> موقع الدراما
            </div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="ابحث عن مسلسل أو فيلم...">
                <button id="searchBtn" class="btn-search"><i class="fas fa-search"></i></button>
            </div>
            <div class="nav-links">
                <a href="index.php" class="active">الرئيسية</a>
                <a href="#" onclick="loadCategory()">التصنيفات</a>
                <a href="#" onclick="loadTrending()">الأكثر مشاهدة</a>
            </div>
        </div>
    </nav>

    <div id="heroSection" class="hero-section">
        <div class="hero-content">
            <h1>مرحبا بك في موقع الدراما</h1>
            <p>اكتشف أفضل المسلسلات والأفلام العربية والأجنبية</p>
        </div>
    </div>

    <div class="container">
        <section class="section">
            <h2>المقترحات المميزة</h2>
            <div id="featuredContainer" class="grid-container">
                <div class="loading">جاري التحميل...</div>
            </div>
        </section>

        <section class="section">
            <h2>الأحدث</h2>
            <div id="latestContainer" class="grid-container">
                <div class="loading">جاري التحميل...</div>
            </div>
        </section>

        <section class="section" id="searchSection" style="display:none;">
            <h2>نتائج البحث</h2>
            <div id="searchResultsContainer" class="grid-container">
                <div class="loading">جاري البحث...</div>
            </div>
        </section>
    </div>

    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="videoPlayer"></div>
            <div id="videoInfo"></div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 موقع الدراما. جميع الحقوق محفوظة.</p>
            <p>يعمل بواسطة <strong>AnyShort API</strong></p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>