<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Inline\Element\Strong;

class SendQiliu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Qiliu:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $disk = Storage::disk('qiniu');
        $fileContents = public_path('storages\coverimg/')."dXwbGumaXRWcIpH4OH6kjFqg8UrxHDeh.jpg";
        //$disk->put('avatars/filename.jpg', $fileContents);
        $url = $disk->getUrl('avatars/filename.jpg');
        dd($url);


    }
}
