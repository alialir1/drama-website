# 🎬 موقع الدراما - Drama Website

موقع عصري لمشاهدة المسلسلات والأفلام العربية والأجنبية باستخدام AnyShort API.

## 🚀 المميزات

✅ واجهة حديثة وسهلة الاستخدام  
✅ دعم اللغة العربية (RTL)  
✅ تصميم ريسبونسيف للهواتف والأجهزة اللوحية  
✅ نظام بحث متقدم  
✅ تصفح المسلسلات والأفلام  
✅ تشغيل الفيديوهات مباشرة  
✅ عرض الترجمات  
✅ نظام نسخ مؤقتة للبيانات (Cache)

## 📋 المتطلبات

- PHP 7.4+
- cURL extension
- حساب على AnyShort.net
- خادم ويب (Apache, Nginx)

## 📦 التثبيت

```bash
# 1. استنساخ المستودع
git clone https://github.com/alialir1/drama-website.git
cd drama-website

# 2. منح صلاحيات المجلدات
chmod 755 cache
chmod 755 assets

# 3. تشغيل الموقع
# على خادم محلي
php -S localhost:8000
```

## 🎯 طريقة الاستخدام

### التشغيل على خادم محلي

```bash
php -S 0.0.0.0:8000
```

ثم افتح متصفحك على `http://localhost:8000`

### الملفات الرئيسية

```
drama-website/
├── index.php              # الصفحة الرئيسية
├── api.php                # وكيل API
├── assets/
│   ├── css/
│   │   └── style.css      # أنماط الموقع
│   ├── js/
│   │   └── main.js        # سكريبتات JavaScript
│   └── images/            # الصور
├── cache/                 # مجلد البيانات المؤقتة
└── README.md             # هذا الملف
```

## 🔧 الوصول إلى API

### الإجراءات المتاحة

#### 1. الصفحة الرئيسية
```
GET api.php?action=home&lang=ar
```

#### 2. البحث
```
GET api.php?action=search&q=نص البحث&lang=ar
```

#### 3. تصفح المحتوى
```
GET api.php?action=browse&lang=ar&limit=20
```

#### 4. معلومات المسلسل
```
GET api.php?action=title&id=36936&lang=ar
```

#### 5. الحلقات
```
GET api.php?action=episodes&id=36936&lang=ar
```

#### 6. تشغيل الحلقة
```
GET api.php?action=play&eid=1917829&lang=ar
```

#### 7. الترجمات
```
GET api.php?action=subtitles&eid=1917829&lang=ar
```

#### 8. الفئات
```
GET api.php?action=tags&lang=ar
```

## 🎨 التخصيص

### تغيير الألوان

عدّل متغيرات CSS في `assets/css/style.css`:

```css
:root {
    --primary-color: #e50914;      /* اللون الأساسي */
    --secondary-color: #221f1f;    /* اللون الثانوي */
    --text-color: #ffffff;          /* لون النصوص */
    --bg-color: #141414;            /* لون الخلفية */
    --card-bg: #2f2f2f;             /* لون بطاقات المحتوى */
}
```

### تغيير اللغة

عدّل متغير `LANG` في `assets/js/main.js`:

```javascript
const LANG = 'en'; // للإنجليزية
const LANG = 'ar'; // للعربية
```

## 🔐 الأمان

- تجنب تخزين كلمات المرور
- استخدم HTTPS على الخوادم الإنتاجية
- قم بتصفية المدخلات والمخرجات
- قم بتحديث المكتبات بانتظام

## 📱 التوافقية

- ✅ Chrome
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ الهواتف الذكية
- ✅ الأجهزة اللوحية

## 🤝 المساهمة

نرحب بمساهماتك! يرجى:

1. عمل Fork للمستودع
2. إنشاء فرع جديد (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى الفرع (`git push origin feature/amazing-feature`)
5. فتح Pull Request

## 📝 الترخيص

هذا المشروع مرخص تحت رخصة MIT. انظر ملف `LICENSE` للتفاصيل.

## ⚠️ تنبيه قانوني

هذا الموقع يستخدم AnyShort API لأغراض تعليمية فقط. تأكد من الامتثال لقوانين النسخ واستخدام المحتوى في بلدك.

## 💬 الدعم

للإبلاغ عن المشاكل أو الاقتراحات:

- 📌 فتح Issue على GitHub
- 📧 التواصل عبر البريد الإلكتروني

## 🙏 شكر خاص

- شكر لـ AnyShort على توفير الـ API
- شكر لجميع المساهمين

---

**استمتع بمشاهدة أفضل المسلسلات! 🎬**