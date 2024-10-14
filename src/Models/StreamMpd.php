<?php

namespace ContentDecryptionPlugin\Models;


use Illuminate\Database\Eloquent\Model;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class StreamMpd extends Model
{
    use Uuid;

    protected $table = 'streams_mpd';

    protected $keyType = 'string';

    protected $guarded = ['id'];

}
