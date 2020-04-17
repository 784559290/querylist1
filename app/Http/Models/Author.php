<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    //
    protected $table = 'author';
    use SoftDeletes;//开启软删除也就是逻辑删除用的
    protected $guarded = [];//用法 不允许字段为空，即允许所有字段

    //根据名称返回
    public static function findname($name){

       $Author= self::where('authorname',$name)->first();

       if ($Author){
           return $Author;
       }else{
           self::createAu($name);
           return self::where('authorname',$name)->first();
       }
    }

    public static function createAu($name){
        self::create(['authorname' => $name]);
    }
}

