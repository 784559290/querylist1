<?php


namespace App\Http\Controllers\Reptile;

use App\Http\Models\Novechapter;
use App\Http\Models\Novel;
use Illuminate\Validation\Rule;
use QL\QueryList;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class BiqugeController extends Controller
{

    private $url = "http://www.xbiquge.la";

    /**
     * @name 章节信息
     */
    public function information($data=[])
    {

        $url = $this->url . $data['url'];
        try {
            $html = QueryList::get($url)->find('#wrapper')->html();
        } catch (RequestException $e) {
           return $e->getRequest();
        }


        $Novel = QueryList::html($html)->find('.box_con:eq(0)');
        $src = $Novel->find('#fmimg img')->src; //封面图片
        $addNovel['noName'] = $Novel->find('#info h1')->html();
        $addNovel['noNickName'] = $Novel->find('#info p:eq(0)')->html();
        $Newest = $Novel->find('#info p:eq(3)')->html();
        preg_match("/\：(.*)/iS", $addNovel['noNickName'], $arr);
        $addNovel['noNickName'] = $arr[1];
        $addNovel['introduce'] = $Novel->find('#intro p:eq(1)')->html();
        //preg_match("/\.html\\\">(.*)<\/a>/iS",$Newest,$arr);
        $ResultDb = NovelController::addNovel($addNovel,$src);
        $list = $Novel->find('#list');

        if ($ResultDb['type'] === 0) {
            $this->Catalog($html,$ResultDb['content']->id);
        }else{
            return $this->returnJson(001, $ResultDb);
        }
    }





    //添加章节目录
    public function chapter($data = [])
    {
        try {
            $url = $this->url . $data['url'];
            $html = QueryList::get($url)->find('#content');
            $html->find('p')->remove();
            $data['chapterContent'] = $html->html();
            $data['type'] = 1;
        } catch (RequestException $e) {
            $data['chapterContent'] = '失败';
            $data['type'] = 0;
        }

        if (empty($html)) {
            $data['chapterContent'] = '失败';
            $data['type'] = 0;
        }

        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();
        if (Cache('createArr')){
            $createArr = Cache('createArr');
            $createArr[] = $data;
            Cache(['createArr' => $createArr],now()->addMinutes(10));
        }else{
            $createArr[] = $data;
            Cache(['createArr' => $createArr],now()->addMinutes(10));
        }
        if (count($createArr) ==10){
            Novechapter::insert($createArr);
            $createArr = false;
            Cache(['createArr' => $createArr],now()->addMinutes(10));
        }
    }


    /**
     * @name 章节目录
     */
    public function Catalog($html,$id)
    {
        $config = $this->CatalogHtml();
        $Novel = QueryList::html($html)->rules($config['rules'])->range($config['range'])->query()->getData();
        foreach ($Novel as $k => $v){
            $catalogarr['url']= $v['link'];
            $catalogarr['chapterName']= $v['title'];
            $catalogarr['sort']= $k;
            $catalogarr['noid'] = $id;
            $jobs = (new \App\Jobs\BiqugePodcast($catalogarr))->delay(now()->addSeconds($k+3));
            $this->dispatch($jobs);
        }

    }

    public function CatalogHtml()
    {
        // 元数据采集规则
        $rules = [
            // 采集文章标题
            'title' => ['a', 'text'],
            // 采集链接
            'link' => ['a', 'href'],
            // 采集缩略图
            //'img' => ['.list_thumbnail>img', 'src'],
            // 采集文档简介
            /*'desc' => ['.memo', 'text']*/
        ];
        // 切片选择器
        $range = 'dl dd';
        return ['range'=>$range,'rules'=>$rules];
    }
    /**
     * @name 添加小说
     */
    public function addNovel()
    {

        $data = request()->input();
        $rules = [
            'url' => 'required',
        ];
        $messages = [
            'required' => ':attribute不能为空',
        ];
        $attributes = [
            'url' => '小说地址',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            return $this->returnJson(1, $validator->errors()->first());     //显示第一条错误
        }

        $this->information($data);

    }

    /**
     * 更新小说
     */
    public function upNovel(){
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

        $novel = Novechapter::Join('novel','novel.noid','=','novechapter.noid')
             ->orderBy('novechapter.sort','desc')
            ->where('novel.noid', $data['noid'])->first(['nourl','chapterName','sort']);

        $url = $this->url . $novel->nourl;
        try {
            $html = QueryList::get($url)->find('#wrapper')->html();
        } catch (RequestException $e) {
            return $e->getRequest();
        }
        $config = $this->CatalogHtml();
        $Novel = QueryList::html($html)->rules($config['rules'])->range($config['range'])->query()->getData();

        $type = false;
        $k = 0;
        foreach ($Novel as $value) {
            if ($value['title'] == $novel->chapterName){
                $type = true;
                continue;
            }
            if ($type){
                $k += 1;
                $catalogarr['url']= $value['link'];
                $catalogarr['chapterName']= $value['title'];
                $catalogarr['sort']= $novel->sort+=1;
                $catalogarr['noid'] = $data['noid'];

                $jobs = (new \App\Jobs\BiqugePodcast($catalogarr))->delay(now()->addSeconds($k));
                $this->dispatch($jobs);
            }
        }


    }
}
