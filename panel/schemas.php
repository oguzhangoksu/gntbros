<?php
declare(strict_types=1);

/**
 * Panel alan tanımları. Her bölüm bir data/*.json dosyasını düzenler.
 *
 * Alan tipleri: text, textarea, url, email, number, password, checkbox, select,
 *               image, video, media (görsel ya da video), slug, group, repeater
 * i18n => true olan alanlar {"tr": "...", "en": "..."} olarak saklanır.
 */

function f(string $key, string $label, string $type = 'text', array $opt = []): array
{
    return ['key' => $key, 'label' => $label, 'type' => $type] + $opt;
}

function i18n(string $key, string $label, string $type = 'text', array $opt = []): array
{
    return f($key, $label, $type, ['i18n' => true] + $opt);
}

function group(string $key, string $label, array $fields, array $opt = []): array
{
    return f($key, $label, 'group', ['fields' => $fields] + $opt);
}

function repeater(string $key, string $label, array $fields, array $opt = []): array
{
    return f($key, $label, 'repeater', ['fields' => $fields] + $opt);
}

function seo_group(string $key, string $label): array
{
    return group($key, $label, [
        i18n('title', 'Sayfa başlığı (title)', 'text', ['help' => 'Arama sonuçlarında görünen başlık. 50–60 karakter idealdir.']),
        i18n('description', 'Açıklama (meta description)', 'textarea', ['rows' => 2, 'help' => '150–160 karakter idealdir.']),
    ]);
}

function panel_sections(): array
{
    $productCats = array_map(
        fn($c) => ['value' => $c['slug'], 'label' => $c['name']['tr'] ?? $c['slug']],
        Store::get('products')['categories'] ?? []
    );

    return [
        'home' => [
            'title' => 'Anasayfa',
            'file'  => 'home',
            'icon'  => 'home',
            'fields' => [
                group('hero', 'Üst alan (hero)', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 3]),
                    i18n('button', 'Kırmızı buton yazısı', 'text', ['half' => true, 'help' => 'İletişim sayfasına gider.']),
                    i18n('button2', 'Çerçeveli buton yazısı', 'text', ['half' => true, 'help' => 'Uygulamalar sayfasına gider.']),
                    f('video', 'Arka plan videosu', 'video', ['help' => 'Sağ taraftaki çerçevede oynar. Boş bırakılırsa kapak görseli gösterilir.']),
                    f('poster', 'Kapak görseli', 'image', ['help' => 'Video yüklenene kadar ve video yoksa gösterilir. Paylaşım önizlemelerinde de kullanılır.']),
                    f('dim_x', 'Üstteki ölçü yazısı', 'text', ['half' => true, 'help' => 'Örn: Ø 3 – 650 mm. Boş bırakılırsa çizgi gösterilmez.']),
                    f('dim_y', 'Sağdaki ölçü yazısı', 'text', ['half' => true, 'help' => 'Örn: 1100 mm. Boş bırakılırsa çizgi gösterilmez.']),
                    i18n('dim_caption', 'Ölçü açıklaması'),
                ]),
                group('services', 'Ne yapıyoruz', [
                    i18n('title', 'Bölüm başlığı'),
                    repeater('items', 'Hizmetler', [
                        i18n('title', 'Başlık'),
                        i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                        f('image', 'Görsel', 'image'),
                    ], ['item_label' => 'title']),
                ]),
                group('about', 'Hakkımızda bloğu', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Metin', 'textarea'),
                    f('video', 'Yanındaki görsel ya da video', 'media'),
                    repeater('points', 'Maddeler', [
                        i18n('title', 'Başlık'),
                        i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                    ], ['item_label' => 'title']),
                ]),
                group('grinding', 'Taşlama kapasitesi tablosu', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                    repeater('items', 'Satırlar', [
                        i18n('title', 'Yöntem'),
                        i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                        i18n('capacity', 'Kapasite', 'text', ['help' => 'Örn: Ø 3 – 650 mm, 1100 mm boy']),
                        f('image', 'Görsel', 'image'),
                    ], ['item_label' => 'title']),
                ]),
                group('sectors', 'Çalıştığımız sektörler', [
                    i18n('title', 'Başlık'),
                    f('video', 'Yanındaki görsel ya da video', 'media'),
                    repeater('items', 'Sektörler', [i18n('name', 'Sektör adı')], ['item_label' => 'name', 'compact' => true]),
                ]),
                group('cta', 'Alt çağrı alanı (tüm sayfalarda)', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Metin', 'textarea', ['rows' => 3]),
                    i18n('button', 'Buton yazısı', 'text', ['half' => true]),
                    f('image', 'Görsel (yalnızca anasayfada)', 'image'),
                ]),
            ],
        ],

        'pages' => [
            'title' => 'Kurumsal sayfalar',
            'file'  => 'pages',
            'icon'  => 'doc',
            'fields' => [
                group('about', 'Hakkımızda', [
                    i18n('title', 'Başlık'),
                    i18n('lead', 'Giriş cümlesi'),
                    i18n('text', 'Metin', 'textarea', ['rows' => 10, 'help' => 'Paragrafları boş bir satırla ayırın.']),
                    f('image', 'Görsel', 'media'),
                ]),
                group('vision', 'Vizyon & Misyon', [
                    i18n('vision_title', 'Vizyon başlığı'),
                    i18n('vision_text', 'Vizyon metni', 'textarea', ['rows' => 3]),
                    i18n('mission_title', 'Misyon başlığı'),
                    i18n('mission_text', 'Misyon metni', 'textarea', ['rows' => 3]),
                    repeater('info', 'Bilgi blokları', [
                        i18n('title', 'Başlık'),
                        i18n('text', 'Metin', 'textarea', ['rows' => 3]),
                    ], ['item_label' => 'title']),
                    f('image', 'Görsel', 'media'),
                    repeater('uses', 'Kullanım alanları', [i18n('name', 'Alan')], ['item_label' => 'name', 'compact' => true]),
                    i18n('features_title', 'Özellikler başlığı'),
                    repeater('features', 'Özellikler', [
                        i18n('title', 'Başlık'),
                        i18n('points', 'Maddeler', 'textarea', ['rows' => 3, 'help' => 'Her satır bir madde.']),
                    ], ['item_label' => 'title']),
                ]),
                group('policy', 'Kuruluş Politikamız', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Metin', 'textarea', ['rows' => 10, 'help' => 'Paragrafları boş bir satırla ayırın.']),
                    repeater('principles', 'İlkeler', [i18n('name', 'İlke')], ['item_label' => 'name', 'compact' => true]),
                    f('image', 'Görsel', 'media'),
                ]),
            ],
        ],

        'applications' => [
            'title' => 'Uygulamalar',
            'file'  => 'applications',
            'icon'  => 'gear',
            'fields' => [
                group('intro', 'Sayfa girişi', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 3]),
                ]),
                repeater('items', 'Uygulamalar', [
                    f('active', 'Yayında', 'checkbox', ['default' => true]),
                    i18n('title', 'Başlık'),
                    i18n('capacity', 'Kapasite'),
                    i18n('text', 'Metin', 'textarea', ['rows' => 5, 'help' => 'Paragrafları boş bir satırla ayırın.']),
                    f('media', 'Görsel ya da video', 'media'),
                ], ['item_label' => 'title']),
            ],
        ],

        'products' => [
            'title' => 'Ürünler',
            'file'  => 'products',
            'icon'  => 'box',
            'fields' => [
                group('intro', 'Sayfa girişi', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 3]),
                ]),
                f('per_page', 'Sayfa başına ürün sayısı', 'number', ['tab' => 'Ürünler', 'half' => true, 'min' => 1, 'max' => 60, 'default' => 10, 'help' => 'Ürün sayısı bu adedi geçerse liste sayfalara bölünür. Masaüstünde satırlar 3 kart olduğu için 9, 12 ya da 15 en düzenli görünür.']),
                repeater('categories', 'Kategoriler', [
                    f('active', 'Yayında', 'checkbox', ['default' => true]),
                    i18n('name', 'Kategori adı'),
                    f('slug', 'Adres (URL)', 'slug', ['from' => 'name', 'help' => 'Boş bırakılırsa addan üretilir. Değiştirirseniz ürünlerin kategorisini de güncelleyin.']),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                ], ['item_label' => 'name', 'note' => 'Yeni eklenen kategori, kaydettikten sonra ürünlerdeki kategori listesinde görünür.']),
                repeater('items', 'Ürünler', [
                    f('active', 'Yayında', 'checkbox', ['default' => true]),
                    i18n('name', 'Ürün adı'),
                    f('slug', 'Adres (URL)', 'slug', ['half' => true, 'from' => 'name', 'help' => 'Boş bırakılırsa ürün adından üretilir.']),
                    f('category', 'Kategori', 'select', ['half' => true, 'options' => $productCats]),
                    i18n('summary', 'Kısa açıklama (liste için)', 'textarea', ['rows' => 2]),
                    i18n('description', 'Detay metni', 'textarea', ['rows' => 6, 'help' => 'Paragrafları boş bir satırla ayırın.']),
                    repeater('images', 'Görseller', [f('image', 'Görsel', 'image')], ['compact' => true]),
                    repeater('specs', 'Teknik bilgiler', [
                        i18n('label', 'Özellik'),
                        i18n('value', 'Değer'),
                    ], ['item_label' => 'label', 'compact' => true]),
                ], ['item_label' => 'name', 'collapsed' => true]),
            ],
        ],

        'gallery' => [
            'title' => 'Galeri & referanslar',
            'file'  => 'gallery',
            'icon'  => 'image',
            'fields' => [
                group('intro', 'Sayfa girişi', [
                    i18n('title', 'Başlık'),
                    i18n('text', 'Açıklama', 'textarea', ['rows' => 2]),
                ]),
                repeater('items', 'Galeri görselleri', [
                    f('active', 'Yayında', 'checkbox', ['default' => true]),
                    f('image', 'Görsel', 'image'),
                    i18n('caption', 'Açıklama'),
                ], ['item_label' => 'caption', 'grid' => true]),
                i18n('references_title', 'Bölüm başlığı', 'text', ['tab' => 'Referanslar']),
                repeater('references', 'Referanslar', [
                    f('active', 'Yayında', 'checkbox', ['default' => true]),
                    f('name', 'Firma adı'),
                    f('logo', 'Logo', 'image'),
                    f('url', 'Web sitesi', 'url'),
                ], ['item_label' => 'name', 'note' => 'Referans eklenmediği sürece galeri sayfasında bu bölüm görünmez.']),
            ],
        ],

        'settings' => [
            'title' => 'Genel ayarlar',
            'file'  => 'settings',
            'icon'  => 'cog',
            'fields' => [
                group('site', 'Site', [
                    f('name', 'Marka adı'),
                    f('legal_name', 'Ticari ünvan'),
                    i18n('tagline', 'Kısa tanım'),
                    f('logo', 'Logo', 'image', ['help' => 'Boş bırakılırsa marka adı yazı logosu olarak gösterilir. Yatay, şeffaf PNG önerilir.']),
                    f('favicon', 'Site ikonu (favicon)', 'image'),
                    i18n('footer_text', 'Alt bilgi metni', 'textarea', ['rows' => 2]),
                ]),
                group('contact', 'İletişim', [
                    repeater('phones', 'Telefonlar', [f('number', 'Numara')], ['item_label' => 'number', 'compact' => true, 'help' => 'İlk numara üst menüdeki arama butonunda kullanılır.']),
                    f('email', 'E-posta', 'email'),
                    i18n('address', 'Adres', 'textarea', ['rows' => 2]),
                    i18n('hours', 'Çalışma saatleri', 'textarea', ['rows' => 2, 'help' => 'Boş bırakılırsa gösterilmez.']),
                    f('whatsapp', 'WhatsApp numarası', 'text', ['help' => 'Boş bırakılırsa WhatsApp butonu gizlenir.']),
                    f('map_embed', 'Google Maps embed adresi', 'url', ['help' => 'Google Maps > Paylaş > Harita yerleştir > iframe içindeki src adresi.']),
                ]),
                group('social', 'Sosyal medya', [
                    f('instagram', 'Instagram', 'url'),
                    f('linkedin', 'LinkedIn', 'url'),
                    f('youtube', 'YouTube', 'url'),
                    f('facebook', 'Facebook', 'url'),
                ]),
                group('seo', 'Arama motoru (SEO)', [
                    seo_group('home', 'Anasayfa'),
                    seo_group('about', 'Hakkımızda'),
                    seo_group('vision', 'Vizyon & Misyon'),
                    seo_group('policy', 'Kuruluş Politikamız'),
                    seo_group('applications', 'Uygulamalar'),
                    seo_group('products', 'Ürünler'),
                    seo_group('gallery', 'Galeri'),
                    seo_group('contact', 'İletişim'),
                ], ['collapsed' => true]),
                group('smtp', 'E-posta gönderimi (SMTP)', [
                    f('enabled', 'İletişim formu mesajlarını e-posta ile gönder', 'checkbox'),
                    f('to', 'Mesajların gideceği adres', 'text', ['help' => 'Birden fazla adres için virgülle ayırın.']),
                    f('host', 'SMTP sunucusu', 'text', ['help' => 'Örn: mail.gntbros.com.tr']),
                    f('port', 'Port', 'number', ['half' => true]),
                    f('secure', 'Güvenlik', 'select', ['half' => true, 'options' => [
                        ['value' => 'tls', 'label' => 'STARTTLS (genelde 587)'],
                        ['value' => 'ssl', 'label' => 'SSL (genelde 465)'],
                        ['value' => 'none', 'label' => 'Yok (önerilmez)'],
                    ]]),
                    f('user', 'Kullanıcı adı', 'text', ['half' => true]),
                    f('pass', 'Şifre', 'password', ['half' => true, 'help' => 'Değiştirmek istemiyorsanız boş bırakın.']),
                    f('from', 'Gönderen adresi', 'email', ['help' => 'Boş bırakılırsa kullanıcı adı kullanılır.']),
                    f('from_name', 'Gönderen adı'),
                ], ['collapsed' => true]),
            ],
        ],
    ];
}
