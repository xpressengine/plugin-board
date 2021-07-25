<?php

namespace Xpressengine\Plugins\Board\Commands;

use Illuminate\Console\Application as Artisan;

trait RegisterArtisan
{
    /**
     * 위젯 생성 코멘드를 `Artisan`에 등록합니다.
     *
     * @return void
     */
    public static function boot()
    {
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands(self::class);
        });
    }
}
