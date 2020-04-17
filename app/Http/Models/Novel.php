<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Novel extends Model
{
    //
    protected $table = 'novel';
    use SoftDeletes;//开启软删除也就是逻辑删除用的
    protected $guarded = [];//用法 不允许字段为空，即允许所有字段
    public function Novechapter()
    {
        return $this->hasOne(Novechapter::class,'noid','chid');
    }
}

