# 📮 Adliye PTT İrsaliye Sistemi

Bu proje, Adliyenin PTT kargo ve gönderi süreçlerini dijitalleştirmek, irsaliye oluşturmak ve gönderi takibini kolaylaştırmak için geliştirilmiş modern bir web uygulamasıdır. PHP ve MySQL tabanlı olup, kullanıcı dostu arayüzü ve gelişmiş güvenlik özellikleriyle donatılmıştır.

## ✨ Özellikler

### 📦 İrsaliye ve Gönderi Yönetimi
*   **İrsaliye Oluşturma:** Resmi formatta PTT teslim tutanağı/irsaliyesi oluşturma.
*   **Barkodlu Çıktı:** Otomatik barkod oluşturma ve yazıcı dostu sayfa yapısı.
*   **Gönderi Takibi:** Barkod, evrak no veya alıcı adına göre detaylı sorgulama.

### 👥 Kullanıcı ve Yetki Yönetimi
*   **Rol Tabanlı Erişim:** Yönetici (Admin) ve Standart Kullanıcı rolleri.
*   **Birim Bazlı Ayrım:** Her birim sadece kendi oluşturduğu evrakları görür (Yöneticiler hepsini görür).
*   **Güvenli Oturum:**
    *   Eşzamanlı giriş engelleme (Aynı hesaba farklı yerlerden girilmesini önleme).
    *   IP değişikliği tespiti ve otomatik oturum sonlandırma.

### 🎨 Modern Arayüz ve Tasarım
*   **Koyu Tema (Dark Mode):** Göz yormayan, sistem tercihlerine duyarlı koyu mod desteği.
*   **Dinamik Arka Plan:** Animasyonlu baloncuk efekti ile canlı görünüm.
*   **Responsive Tasarım:** Mobil ve masaüstü uyumlu Bootstrap 5 altyapısı.
*   **Özelleştirilebilir:** Kurum adı ve başlık ayarları panelden değiştirilebilir.

### 🛡️ Loglama ve Güvenlik
*   **İşlem Geçmişi:** Yapılan tüm işlemlerin (Ekleme, Silme, Güncelleme) detaylı kaydı.
*   **Güvenlik Logları:** Yetkisiz giriş denemeleri ve oturum çakışmalarının renk kodlu loglanması.

## 🚀 Kurulum

1.  **Veritabanı Oluşturun:** MySQL üzerinde yeni bir veritabanı açın (örn: `ptt_irsaliye`).
2.  **SQL'i İçe Aktarın:** `database.sql` dosyasını oluşturduğunuz veritabanına import edin.
3.  **Ayarları Yapılandırın:** `config.php` dosyasını açın ve veritabanı bilgilerinizi girin:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ptt_irsaliye');
    define('DB_USER', 'root');
    define('DB_PASS', 'password');
    ```
4.  **Admin Girişi:**
    *   Kullanıcı Adı: `admin`
    *   Şifre: `password` (Güvenliğiniz için kurulumdan sonra değiştirin!)

## 📂 Proje Yapısı

*   `public/` - Web sunucusu kök dizini
*   `assets/` - CSS, JS ve resim dosyaları
*   `templates/` - Tekrar kullanılabilir HTML parça dosyaları
*   `api.php` - AJAX isteklerini karşılayan arka uç servisi
*   `functions.php` - Genel yardımcı fonksiyonlar ve yetki kontrolleri

## 🛠️ Teknolojiler

*   **Backend:** PHP 8+ (PDO)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Frameworks:** Bootstrap 5, SweetAlert2, Flatpickr
*   **Icons:** FontAwesome 6
