<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-posta Doğrulama</title>
</head>
<body>
    <h2>Merhaba {{ $user->name }},</h2>
    <p>Hesabınızı aktifleştirmek için aşağıdaki linke tıklayın:</p>
    <a href="{{ $url }}">{{ $url }}</a>
    <p>Bu link yalnızca bir kez kullanılabilir.</p>
</body>
</html>
