<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 9/01/2016
 * Time: 11:55 AM
 */

namespace GroupBot\Brains;


class Translate
{
    const BASE_URL = 'https://translate.yandex.net/api/v1.5/tr.json/';
    const MESSAGE_UNKNOWN_ERROR = 'Unknown error';
    const MESSAGE_JSON_ERROR = 'JSON parse error';
    const MESSAGE_INVALID_RESPONSE = 'Invalid response from service';

    private $languages = array(
        'sq' => 'Albanian',
        'en' => 'English',
        'ar' => 'Arabic',
        'hy' => 'Armenian',
        'az' => 'Azerbaijan',
        'af' => 'Afrikaans',
        'eu' => 'Basque',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bs' => 'Bosnian',
        'cy' => 'Welsh',
        'vi' => 'Vietnamese',
        'hu' => 'Hungarian',
        'ht' => 'Haitian (Creole)',
        'gl' => 'Galician',
        'nl' => 'Dutch',
        'el' => 'Greek',
        'ka' => 'Georgian',
        'da' => 'Danish',
        'he' => 'Oy Vey!',
        'id' => 'Indonesian',
        'ga' => 'Irish',
        'it' => 'Italian',
        'is' => 'Icelandic',
        'es' => 'Spanish',
        'kk' => 'Kazakh',
        'ca' => 'Catalan',
        'ky' => 'Kyrgyz',
        'zh' => 'Chinese',
        'ko' => 'Korean',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'mk' => 'Macedonian',
        'mn' => 'Mongolian',
        'de' => 'German',
        'no' => 'Norwegian',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sr' => 'Serbian',
        'sk' => 'Slovakian',
        'sl' => 'Slovenian',
        'sw' => 'Swahili',
        'tg' => 'Tajik',
        'th' => 'Thai',
        'tl' => 'Tagalog',
        'tt' => 'Tatar',
        'tr' => 'Turkish',
        'uz' => 'Uzbek',
        'uk' => 'Ukrainian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'sv' => 'Swedish',
        'et' => 'Estonian',
        'ja' => 'Weebish'
    );

    protected $handler;

    public function __construct()
    {
        $this->handler = curl_init();
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
    }

    public function getSupportedLanguages($culture = null)
    {
        return $this->execute('getLangs', array(
            'ui' => $culture
        ));
    }

    private function isKanji($str) {
        return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
    }

    private function isHiragana($str) {
        return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
    }

    private function isKatakana($str) {
        return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
    }

    public function isJapanese($str) {
        return $this->isKanji($str) || $this->isHiragana($str) || $this->isKatakana($str);
    }

    public function detectLanguage($text)
    {
        try {
            $data = $this->execute('detect', array(
                'text' => $text
            ));
        } catch (\Exception $e) {
            return false;
        }
        return isset($this->languages[$data['lang']]) ? $this->languages[$data['lang']] : false;
    }

    public function translate($text, $language, $html = false, $options = 0)
    {
        $data = $this->execute('translate', array(
            'text'    => $text,
            'lang'    => $language,
            'format'  => $html ? 'html' : 'plain',
            'options' => $options
        ));

        $langs = explode('-', $data['lang']);

        return array(
            'source' => $text,
            'result' => $data['text'],
            'lang_source' => $this->languages[$langs[0]],
            'lang_translated' => $this->languages[$langs[1]]
        );
    }

    protected function execute($uri, array $parameters)
    {
        $parameters['key'] = YANDEX_TRANSLATE_KEY;
        curl_setopt($this->handler, CURLOPT_URL, static::BASE_URL . $uri);
        curl_setopt($this->handler, CURLOPT_POST, true);
        curl_setopt($this->handler, CURLOPT_POSTFIELDS, http_build_query($parameters));

        $remoteResult = curl_exec($this->handler);
        if ($remoteResult === false) {
            throw new \Exception(curl_error($this->handler), curl_errno($this->handler));
        }
        $result = json_decode($remoteResult, true);
        if (!$result) {
            $errorMessage = self::MESSAGE_UNKNOWN_ERROR;
            if (version_compare(PHP_VERSION, '5.3', '>=')) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (version_compare(PHP_VERSION, '5.5', '>=')) {
                        $errorMessage = json_last_error_msg();
                    } else {
                        $errorMessage = self::MESSAGE_JSON_ERROR;
                    }
                }
            }
            throw new \Exception(sprintf('%s: %s', self::MESSAGE_INVALID_RESPONSE, $errorMessage));
        } elseif (isset($result['code']) && $result['code'] > 200) {
            throw new \Exception($result['message'], $result['code']);
        }
        return $result;
    }
}