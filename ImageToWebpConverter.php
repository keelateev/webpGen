<?php

/**
 * Клас генерирует из .jpg|.png картинки новую в формате .webp
 */
class ImageToWebpConverter
{
    /**
     * Проверяет и создаёт в случае отсутствия в корне сайта каталог /upload/webp/
     * @return string
     */
    private static function imageDir(): string
    {
        $newImageDir = ($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/upload/' : 'upload/';
        if (!is_dir($newImageDir)) {
            mkdir($newImageDir);
        }

        $newImageDir .= 'webp/';
        if (!is_dir($newImageDir)) {
            mkdir($newImageDir);
        }
        return $newImageDir;
    }

    /**
     * Функция создание webp картинки
     * @throws Exception
     */
    private static function createWebp(string $oldImagePath, string $newImagePath): void
    {
        $info = getimagesize($oldImagePath);
        $mime = $info['mime'] ?? '';
        if ($mime == 'image/png') {
            $image = imagecreatefrompng($oldImagePath);
        } elseif ($mime == 'image/jpeg') {
            $image = imagecreatefromjpeg($oldImagePath);
        } else {
            throw new Exception('Формат входного файла не поддерживается');
        }
        if (!is_file($newImagePath) || (filectime($oldImagePath) > filectime($newImagePath))) {
            imagewebp($image, $newImagePath, 100);
            imagedestroy($image);
            if (filesize($newImagePath) % 2 == 1) {
                file_put_contents($newImagePath, chr(0x00), FILE_APPEND);
            }
        }
    }

    /**
     * Функция проверяет доступны ли webp в браузере, а так же не создавалась ли ранее из данной картинки webp версия
     * @param string $oldImagePath
     * @param bool $webpBrowserAccepted
     * @return string
     * @throws Exception - ошибка формата входного файла
     */
    public function gen(string $oldImagePath, bool $webpBrowserAccepted = false): string
    {
        /**
         * 1 проверка на доступность webp формата - прямое указание при генерации,
         * используется, когда данные нужно получить через ajax, т.к. в ajax полностью не передаётся $_SERVER пользователя
         */
        if (!$webpBrowserAccepted && !empty($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
            $webpBrowserAccepted = (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false || strpos(
                    $_SERVER['HTTP_USER_AGENT'],
                    ' Chrome/'
                ) !== false);
        }

        /**
         * 2 проверка на доступность webp формата - данные из $_SERVER,
         */
        if (!$webpBrowserAccepted) {
            return $oldImagePath;
        }

        $imageName = basename($oldImagePath);
        $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . $oldImagePath;

        $newImagePath = self::imageDir() . substr($oldImagePath, 0, strrpos($imageName, '.')) . '.webp';

        if (file_exists($oldImagePath) &&
            (!is_file($newImagePath) || (filectime($oldImagePath) > filectime($newImagePath)))) {
            self::createWebp($oldImagePath, $newImagePath);
        }

        return $newImagePath;
    }

    public function remove(string $oldImagePath)
    {
    }
}