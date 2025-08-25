<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigAccount extends Model
{
    public function user(){
        return $this->belongsTo(User::class, 'user_id'); // user_id é a FK na tabela config_accounts
    }
}
