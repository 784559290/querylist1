<?php

namespace App\Jobs;

use App\Http\Controllers\Reptile\BiqugeController;
use App\Http\Controllers\Reptile\NovelController;
use App\Http\Models\Novechapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BiqugeFailPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private  $data;
    private  $id;
    public function __construct($data,$id)
    {
        //
        $this->data = $data;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $big = new NovelController();
        $big->updatefail($this->id,$this->data);

    }



}
