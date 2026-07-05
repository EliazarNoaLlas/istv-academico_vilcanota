<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_remitente',
        'id_destinatario',
        'asunto',
        'mensaje',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_remitente');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_destinatario');
    }
}
