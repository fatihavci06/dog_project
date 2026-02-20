<!DOCTYPE html>
<html>
<head>
    <title>🚨 Yeni Profil Şikayeti</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        h3 { margin-top: 0; color: #d9534f; }
    </style>
</head>
<body>
    <h2>Sistemde yeni bir profil şikayeti oluşturuldu!</h2>

    <div class="box">
        <h3>🛑 Şikayet Eden Kullanıcı</h3>
        <p><strong>Ad Soyad:</strong> {{ $flag->reporter->name ?? 'Bilinmiyor' }}</p>
        <p><strong>E-posta:</strong> {{ $flag->reporter->email ?? 'Bilinmiyor' }}</p>
        <p><strong>Kullanıcı ID:</strong> {{ $flag->reporter_id }}</p>
    </div>

    <div class="box">
        <h3>🐶 Şikayet Edilen Profil</h3>
        <p><strong>Profil Adı:</strong> {{ $flag->flaggedProfile->name ?? 'İsimsiz Profil' }}</p>
        <p><strong>Cinsiyet:</strong> {{ $flag->flaggedProfile->sex ?? 'Belirtilmemiş' }}</p>
        <p><strong>Profil ID:</strong> {{ $flag->flagged_profile_id }}</p>

        </div>

    <br>
    <p>Lütfen admin panelinden ilgili profili inceleyerek gerekli aksiyonu alınız.</p>
</body>
</html>
