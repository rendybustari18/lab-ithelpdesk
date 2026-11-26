<?php

namespace App\Libraries;

final class AttachmentPolicy
{
    public static function validContent(string $path, string $extension): bool
    {
        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $image = @getimagesize($path);
            return $image !== false && (($extension === 'png' && $image[2] === IMAGETYPE_PNG) || ($extension !== 'png' && $image[2] === IMAGETYPE_JPEG));
        }
        if ($extension !== 'pdf') return false;
        $stream = fopen($path, 'rb');
        if ($stream === false) return false;
        $signature = fread($stream, 5);
        fclose($stream);
        return $signature === '%PDF-';
    }
}
