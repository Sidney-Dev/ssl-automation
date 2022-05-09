<?php

namespace App\Models;

use App\Builders\LetsEncryptCertificateBuilder;
use App\Collections\LetsEncryptCertificateCollection;
use App\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $domain
 * @property string|null $fullchain_path
 * @property string|null $chain_path
 * @property string|null $privkey_path
 * @property string|null $cert_path
 * @property bool $created
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $has_expired
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate query()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate newQuery()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate newModelQuery()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate withTrashed()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate withoutTrashed()
 * @method static \App\Builders\LetsEncryptCertificateBuilder|\App\Models\LetsEncryptCertificate whereDomain($value)
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 * @method static static create(array $attributes)
 * @mixin \Eloquent
 */
class LetsEncryptCertificate extends Model
{
    use SoftDeletes;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($domain) {
            $domain->renew();
        });
    }

    protected $guarded = ['id'];

    protected $dates = ['last_renewed_at'];

    protected $casts = [
        'created' => 'boolean',
    ];

    public function newEloquentBuilder($query): LetsEncryptCertificateBuilder
    {
        return new LetsEncryptCertificateBuilder($query);
    }

    public function newCollection(array $models = [])
    {
        return new LetsEncryptCertificateCollection($models);
    }

    public function getHasExpiredAttribute(): bool
    {
        return $this->last_renewed_at && $this->last_renewed_at->diffInDays(now()) >= 90;
    }

    public function renew()
    {
        return LetsEncrypt::renew($this);
    }

    public function renewNow(): self
    {
        return LetsEncrypt::renewNow($this);
    }
}
