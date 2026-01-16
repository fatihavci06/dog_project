<?php
return [
    'fullname_required' => 'İsim alanı zorunludur.',
    'email_required'    => 'E-posta alanı zorunludur.',
    'email_invalid'     => 'Geçerli bir e-posta adresi giriniz.',
    'email_unique'      => 'Bu e-posta zaten kullanımda.',

    'password_required' => 'Şifre alanı zorunludur.',
    'password_min'      => 'Şifre en az :min karakter olmalıdır.',
    'password_confirmed' => 'Şifre doğrulaması eşleşmiyor.',

    'role_required'     => 'Rol alanı zorunludur.',
    'role_invalid'      => 'Geçersiz rol seçildi.',

    'privacy_required'  => 'Gizlilik politikası kabul edilmelidir.',
    'privacy_boolean'   => 'Gizlilik politikası değeri geçersiz.',
    'max_images' => 'En fazla 6 fotoğraf yükleyebilirsiniz.',
    'images_total_size' => 'Fotoğrafların toplam boyutu 60 MB\'ı geçemez.',
    'incorrect_credentials' => 'Gönderilen bilgiler yanlış.',
    'current_password_incorrect' => 'Mevcut şifre yanlış.',
    'password_same' => 'Yeni şifre, eski şifre ile aynı olamaz.',
    'feedback' => [
        'category_invalid' => 'Geçersiz kategori seçildi.',
        'rating_max'       => 'Puan en fazla :max olabilir.',
        'image_invalid'    => 'Görsel base64 formatında ve geçerli bir resim olmalıdır.',
    ],

    'required' => ':attribute alanı gereklidir.',
    'date_format' => ':attribute, :format formatında olmalıdır.',
    'after_or_equal' => ':attribute, :date tarihinden sonra veya aynı gün olmalıdır.',
    'after' => ':attribute, :date zamanından sonra olmalıdır.',
    'regex' => ':attribute formatı geçersiz.',
    'string' => ':attribute metin formatında olmalıdır.',
    'boolean' => ':attribute alanı doğru veya yanlış (true/false) olmalıdır.',
    'numeric' => ':attribute sayısal bir değer olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',

    /*
    |--------------------------------------------------------------------------
    | Özelleştirilmiş Alan İsimleri (Attributes)
    |--------------------------------------------------------------------------
    | Burada veritabanı sütun isimlerini (start_date) kullanıcı dostu
    | isimlere (Başlangıç Tarihi) çeviriyoruz.
    */

    'attributes' => [
        'title' => 'Plan başlığı',
        'start_date' => 'Başlangıç tarihi',
        'end_date' => 'Bitiş tarihi',
        'start_time' => 'Başlangıç saati',
        'end_time' => 'Bitiş saati',
        'color' => 'Plan rengi',
        'location' => 'Konum',
        'lang' => 'Enlem (Latitude)',
        'long' => 'Boylam (Longitude)',
        'notes' => 'Notlar',
        'icon' => 'İkon',
        'participant_id' => 'Katılımcı',
    ],

];
