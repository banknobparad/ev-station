<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function compressAndStoreImage(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $relativePath = trim($directory, '/') . '/' . $file->hashName();
        $storagePath = storage_path('app/' . $disk . '/' . $relativePath);

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']) || !function_exists('getimagesize')) {
            return $file->store($directory, $disk);
        }

        $sourcePath = $file->getRealPath();
        if (!$sourcePath) {
            return $file->store($directory, $disk);
        }

        $info = @getimagesize($sourcePath);
        if (!$info) {
            return $file->store($directory, $disk);
        }

        $mime = $info['mime'];
        $image = false;

        // ดักจับ Error กรณีที่ไฟล์ภาพเสียหายหรือกิน Memory เกินลิมิต
        try {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($sourcePath);
                    break;
                case 'image/webp':
                    $image = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false;
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($sourcePath);
                    break;
                default:
                    $image = false;
            }
        } catch (\Throwable $e) {
            // หากการสร้างรูปภาพล้มเหลว ให้ค่า $image เป็น false
            $image = false;
        }

        // ถ้าไม่สามารถ process ภาพได้ (เช่นภาพพัง) ให้บันทึกไฟล์ต้นฉบับแทนการโยน Error
        if (!$image) {
            return $file->store($directory, $disk);
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1920;
        $maxHeight = 1080;
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);

        if ($ratio < 1) {
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
            $compressed = imagecreatetruecolor($newWidth, $newHeight);

            if (in_array($mime, ['image/png', 'image/gif', 'image/webp'])) {
                imagecolortransparent($compressed, imagecolorallocatealpha($compressed, 0, 0, 0, 127));
                imagealphablending($compressed, false);
                imagesavealpha($compressed, true);
            }

            imagecopyresampled($compressed, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $compressed;
        }

        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $quality = 85;
        $sizeLimit = 2048 * 1024;
        $this->saveCompressedImage($image, $storagePath, $mime, $quality);

        while (filesize($storagePath) > $sizeLimit && $quality > 40) {
            $quality -= 10;
            $this->saveCompressedImage($image, $storagePath, $mime, $quality);
        }

        imagedestroy($image);

        return $relativePath;
    }

    private function saveCompressedImage($image, string $path, string $mime, int $quality): void
    {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($image, $path, $quality);
                break;
            case 'image/png':
                $pngQuality = (int) round((100 - $quality) / 11.111111);
                imagepng($image, $path, min(max($pngQuality, 0), 9));
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($image, $path, $quality);
                } else {
                    imagejpeg($image, $path, $quality);
                }
                break;
            case 'image/gif':
                imagegif($image, $path);
                break;
            default:
                imagejpeg($image, $path, $quality);
                break;
        }
    }
}
