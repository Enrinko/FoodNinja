<?php

namespace App\Services;

use App\Models\Link;

class ShortCodeGenerator
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generate a random, URL-safe short code that is unique across links.
     */
    public function generate(int $length = 6): string
    {
        do {
            $code = $this->randomCode($length);
        } while (Link::where('short_code', $code)->exists());

        return $code;
    }

    private function randomCode(int $length): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }
}
