# Öğrenci Kabul Sınavı Başvuru Sistemi

Bu proje, bir eğitim kurumu için geliştirilmiş **Öğrenci Kabul Sınavı Başvuru Sistemi**dir. Öğrencilerin online olarak sınava başvurması, sınav giriş belgesi oluşturması ve yöneticilerin bu başvuruları yönetmesi amacıyla hazırlanmıştır.

## Özellikler

### Öğrenci Arayüzü
- **Başvuru Formu:** TC Kimlik no, ad, soyad ve diğer gerekli bilgilerle başvuru yapma.
- **TC Kimlik Doğrulama:** 11 haneli ve sayısal kontrol.
- **Mükerrer Kayıt Kontrolü:** Aynı TC ile birden fazla başvuruyu engelleme.
- **KVKK Onayı:** Zorunlu KVKK metni onayı.
- **Sorgulama Ekranı:** Başvuru durumunu sorgulama ve giriş belgesi indirme.
- **PDF Giriş Belgesi:** Başvuru sonrası otomatik sınav giriş belgesi (PDF) oluşturma ve indirme.

### Yönetim Paneli (Admin)
- **Dashboard:** Toplam ve günlük başvuru istatistikleri, son başvurular.
- **Başvuru Yönetimi:** Başvuruları listeleme, düzenleme ve silme.
- **Excel/PDF Dışa Aktarma:** Başvuru listelerini toplu olarak dışa aktarma.
- **Ayarlar:** Okul adı, logo, sınav tarihi, kurallar ve iletişim bilgilerini güncelleme.
- **Sistem Açma/Kapama:** Başvuru sistemini tek tuşla aktif/pasif yapma.

## Kurulum

1. **Dosyaları Yükleyin:** Proje dosyalarını sunucunuzun kök dizinine (örn: `public_html` veya `htdocs`) yükleyin.
2. **Veritabanı Oluşturun:**
   - phpMyAdmin veya benzeri bir araçla yeni bir veritabanı oluşturun (örn: `basvuru_db`).
   - `database.sql` dosyasını bu veritabanına içe aktarın (Import).
3. **Veritabanı Bağlantısını Ayarlayın:**
   - `config.php` dosyasını açın.
   - Veritabanı bilgilerinizi (host, dbname, user, password) güncelleyin.
   ```php
   $host = 'localhost';
   $dbname = 'veritabani_adi';
   $username = 'kullanici_adi';
   $password = 'sifre';
   ```
4. **Yönetici Girişi:**
   - Yönetim paneline `admin/` dizininden erişebilirsiniz.
   - Varsayılan Giriş Bilgileri (Veritabanında tanımlıysa):
     - Kullanıcı Adı: `admin` (veya veritabanındaki tabloda ne varsa)
     - Şifre: *(Veritabanı hash'ine bağlıdır, ilk kurulum için `admin` tablosunu kontrol edin)*

## Kullanılan Teknolojiler

- **Backend:** PHP (PDO)
- **Veritabanı:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, Bootstrap 5, jQuery
- **PDF Kütüphanesi:** tFPDF (Türkçe karakter desteği için)
- **Diğer Kütüphaneler:** SweetAlert2 (Bildirimler), InputMask (Giriş maskeleme)

## Dizin Yapısı

- `admin/` - Yönetim paneli dosyaları
- `libs/` - Harici kütüphaneler (tFPDF vb.)
- `img/` - Resim ve logolar
- `includes/` - Ortak fonksiyonlar ve dosyalar
- `config.php` - Veritabanı konfigürasyonu
- `index.php` - Ana başvuru sayfası
- `generate_pdf.php` - PDF oluşturma sayfası
