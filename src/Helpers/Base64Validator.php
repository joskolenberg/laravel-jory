<?php


namespace JosKolenberg\LaravelJory\Helpers;


class Base64Validator
{
    /**
     * Check if the data is base64 encoded.
     *
     * Might not tell if it's valid but works well enough
     * to make the distinction between base64 and json.
     * And that's all that we need to know here.
     *
     * @param $data
     * @return bool
     */
    public static function check($data): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data);
    }
}