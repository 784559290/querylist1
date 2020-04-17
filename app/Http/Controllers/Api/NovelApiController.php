<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Models\Novechapter;
use App\Http\Models\Novel;
use App\Http\Requests\NovelAccount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NovelApiController extends  Controller
{


    /*
     * @name 章节目录
     */
    public function Catalog()
    {
        $data = request()->input();
        $rules= [
            'noid' => ['bail', 'required',
                Rule::exists('novechapter')->where(function ($query) {$query->where('deleted_at', null);}),],
        ];
        $messages = [
            'required' => ':attribute不能为空',
            'exists' => ':attribute不存在',
        ];
        $attributes = [
            'noid' => '小说名称',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            return $this->returnJson(1, $validator->errors()->first());
        }
        $Catalog = Novechapter::where('noid',$data['noid'])->orderBy('sort')->get(['chid','chapterName']);
        return $this->returnJson(0, '查询成功',$Catalog);
    }
    /*
     * @name 章节目录
     */
    public function queryNovel()
    {
        $data = request()->input();
        $rules= [
            'noid' => ['bail', 'required',
                Rule::exists('novel')->where(function ($query) {$query->where('deleted_at', null);}),],
        ];
        $messages = [
            'required' => ':attribute不能为空',
            'exists' => ':attribute不存在',
        ];
        $attributes = [
            'noid' => '小说名称',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            return $this->returnJson(1, $validator->errors()->first());
        }

        $Catalog = Novel::where('noid',$data['noid'])->first(['noid','noName','noNickName','introduce','coverimg',]);
        $Catalog->coverimg = 'http://querylist.com/storages/coverimg/' . $Catalog->coverimg;
        return $this->returnJson(0, '查询成功',$Catalog);
    }


    public function Nocontent(){
        $data = request()->input();
        $rules= [
            'chid' => ['bail', 'required',
                Rule::exists('novechapter')->where(function ($query) {$query->where('deleted_at', null);}),],
        ];
        $messages = [
            'required' => ':attribute不能为空',
            'exists' => ':attribute不存在',
        ];
        $attributes = [
            'chid' => '小说id',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            return $this->returnJson(1, $validator->errors()->first());
        }
        $nodata['content'] = Novechapter::where('chid', $data['chid'])->first();
        $nodata['upper'] = Novechapter::where('chid', '<', $data['chid'])->where('noid',$nodata['content']->noid)->orderBy('sort','desc')->first();

        $nodata['lower'] =Novechapter::where('chid', '>',$data['chid'])->where('noid',$nodata['content']->noid)->first();
        return $this->returnJson(0, '成功', $nodata);

    }



}
