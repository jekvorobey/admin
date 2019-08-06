<?php

namespace App\Core;

class Frontend
{
    /**
     * Название куки куда запоминаем переключение.
     *
     * @var string
     */
    public $cookieName = 'frontend_build';
    public $cookieValue = false;

    /**
     * Директория где лежит dev-сборка.
     *
     * @var string
     */
    public $devDir = '/build/';

    /**
     * Флаг принудетльного включения dev-режима сборщика.
     * В начале разработки проекта всегда true, потом в какой-то момент меняется на false и больше не трогается.
     *
     * @var bool
     */
    public $forceDevMode = true;

    /**
     * Директория где лежит production-сборка.
     *
     * @var string
     */
    public $productionDir = '/assets/';

    /**
     * Версия для инвалидации кэша.
     *
     * @var string
     */
    public $version;

    public function __construct()
    {
        $cookie = request()->cookies->get($this->cookieName, '');
        $this->cookieValue = $cookie;
    }

    /**
     * Включен ли режим отладки.
     *
     * @return bool
     */
    public function isInDevMode()
    {
        if (app()->environment('production')) {
            return false;
        }

        if ($this->forceDevMode) {
            return true;
        }

        return $this->cookieValue === 'dev';
    }

    /**
     * Функиця для формирования нужного пути до ресурса из сборщика.
     *
     * @param string $path
     * @param bool $addVersion
     * @return string
     */
    public function path($path, $addVersion = false)
    {
        $resultPath = $this->isInDevMode()
            ? $this->devDir . $path
            : $this->productionDir . $path;

//        if ($addVersion) {
//            $separator = mb_strpos($resultPath, '?') ? '&' : '?';
//            $resultPath .= $separator .'v=' . $this->version;
//        }

        return $resultPath;
    }

    /**
     * Формирование пути до директории со скриптами.
     * Добавляет версию по-умолчанию.
     *
     * @param string $path
     * @param bool $addVersion
     * @return string
     */
    public function scripts($path, $addVersion = true)
    {
        return $this->path("scripts/$path", $addVersion);
    }

    /**
     * Формирование пути до директории со стилями.
     * Добавляет версию по-умолчанию.
     *
     * @param string $path
     * @param bool $addVersion
     * @return string
     */
    public function styles($path, $addVersion = true)
    {
        return $this->path("styles/$path", $addVersion);
    }

    /**
     * Формирование пути до директории с изображениями.
     * Не добавляет версию по-умолчанию.
     *
     * @param string $path
     * @param bool $addVersion
     * @return string
     */
    public function img($path, $addVersion = false)
    {
        return $this->path("img/$path", $addVersion);
    }

    /**
     * Формирование пути до директории с SVG.
     * Не добавляет версию по-умолчанию.
     *
     * @param string $path
     * @param bool $addVersion
     * @return string
     */
    public function svg($path, $addVersion = false)
    {
        return $this->path("img/svg/$path", $addVersion);
    }

    public function handle()
    {
        $key = $this->cookieName;
        if (app()->environment('production') || !request($key, false)) {
            return;
        }

        if (request($key) === 'dev') {
            cookie()->queue(cookie()->make($key, 'dev', 60 * 24));
            $this->cookieValue = 'dev';
        } else {
            cookie()->queue(cookie()->make($key, '', time() - 3600));
            $this->cookieValue = '';
        }
    }

}
