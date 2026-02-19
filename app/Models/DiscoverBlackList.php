<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoverBlackList extends Model
{
    // Tablo adını Laravel otomatik olarak 'discover_black_lists' yapacaktır.
    // Eğer migration'da 'discover_blacklists' (bitişik) yaptıysan alttaki satırı aktif et:
     protected $table = 'discover_blacklists';

    protected $fillable = [
        'user_id',
        'pup_profile_id',
    ];

    /**
     * Bu engeli oluşturan kullanıcı.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Engellenen (gizlenen) köpek profili.
     */
    public function pupProfile(): BelongsTo
    {
        return $this->belongsTo(PupProfile::class);
    }
}
