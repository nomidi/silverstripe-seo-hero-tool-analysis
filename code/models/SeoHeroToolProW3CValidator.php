<?php

class SeoHeroToolProW3CValidator
{
    public static function checkData($Link)
    {
        return self::getValidationResults($Link);
    }

    private static function getValidationResults($Link)
    {
        $opts = [
          'http' => [
            'method' => 'GET',
            'header' => [
              'User-Agent: PHP'
            ]
          ]
        ];
        $context = stream_context_create($opts);
        $json_url = 'https://validator.w3.org/nu/?doc='. urlencode($Link) .'&out=json';
        $json = file_get_contents($json_url, false, $context);
        $json_output = json_decode($json);
        return $json_output;
    }
}
