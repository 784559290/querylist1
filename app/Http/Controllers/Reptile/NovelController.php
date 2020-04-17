<?php


namespace App\Http\Controllers\Reptile;


use App\Http\Controllers\Controller;
use App\Http\Models\Author;
use App\Http\Models\Novechapter;
use App\Http\Models\Novel;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use QL\QueryList;

class NovelController extends Controller
{
    private $url = "http://www.xbiquge.la";

    public static function addNovel($data = [],$src='')
    {
        $rules = [
            'noName' => ['required',
                Rule::unique('novel')->where(function ($query) {
                    $query->where('deleted_at', null);
                })],

        ];
        $messages = [
            'required' => ':attribute不能为空',
            'unique' => ':attribute已经存在',
        ];
        $attributes = [
            'noName' => '小说名称',
        ];

        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            return ['type'=>1,'content'=>$validator->errors()->first()];
        }
        $data['authorId'] = Author::findname($data['noNickName'])->id;
        $data['coverimg'] = Str::random(32) . '.jpg';
        $client = new \GuzzleHttp\Client();//忽略SSL错误
        $path =  public_path('storages\coverimg/').$data['coverimg'];
        $response = $client->request('get',$src, ['save_to' => $path]);  //保存远程url到文件
        if ($response->getStatusCode() != 200) {
            $addNovel['coverimg'] = "";
        }

        return  ['type'=>0,'content'=>Novel::create($data)];
    }

    /**
     * 查找错误
     */
    public  function fail(){
        $fail = Novechapter::where('type', 0)->get(['url','chid','url']);

        foreach ($fail as $k => $v){
            $data['url'] = $this->url . $v->url;

            $jobs = (new \App\Jobs\BiqugeFailPodcast($v->chid,$data))->delay(now()->addSeconds($k+3));
            $this->dispatch($jobs);

        }

    }

    //修改错误
    public function updatefail($data,$id)
    {
        try {
            $url = $data['url'];
            $html = QueryList::get($url)->find('#content');
            $html->find('p')->remove();
            $updatedata['chapterContent'] = $html->html();
            $updatedata['type'] = 1;
        } catch (RequestException $e) {
            $updatedata['chapterContent'] = '失败';
            $updatedata['type'] = 0;
        }


        Novechapter::where('chid', $id)->update(['chapterContent' => $updatedata['chapterContent'], 'type' =>  $updatedata['type']]);
    }




}
