<?php

namespace Teg\Modules;

use Stichoza\GoogleTranslate\GoogleTranslate;

trait TranslateModule
{
    private $language_codes = [
        "ab", // Абхазский
        "aa", // Афар
        "af", // Африкаанс
        "ak", // Акан
        "sq", // Албанский
        "am", // Амхарский
        "ar", // Арабский
        "an", // Арагонский
        "hy", // Армянский
        "as", // Ассамский
        "av", // Аварский
        "ae", // Авестийский
        "ay", // Аймара
        "az", // Азербайджанский
        "ba", // Башкирский
        "bm", // Бамбара
        "eu", // Баскский
        "be", // Белорусский
        "bn", // Бенгальский
        "bh", // Бихари
        "bi", // Бислама
        "bs", // Боснийский
        "br", // Бретонский
        "bg", // Болгарский
        "my", // Бирманский
        "ca", // Каталанский
        "ch", // Чаморро
        "ce", // Чеченский
        "zh", // Китайский
        "cu", // Церковнославянский
        "cv", // Чувашский
        "kw", // Корнский
        "co", // Корсиканский
        "cr", // Кри
        "cs", // Чешский
        "da", // Датский
        "de", // Немецкий
        "dv", // Дивехи
        "nl", // Нидерландский
        "dz", // Дзонг-кэ
        "en", // Английский
        "eo", // Эсперанто
        "et", // Эстонский
        "ee", // Эве
        "fo", // Фарерский
        "fj", // Фиджийский
        "fi", // Финский
        "fr", // Французский
        "fy", // Фризский
        "ff", // Фула
        "gd", // Шотландский гэльский
        "ga", // Ирландский
        "gl", // Галисийский
        "gv", // Мэнский
        "el", // Греческий
        "gn", // Гуарани
        "gu", // Гуджарати
        "ht", // Гаитянский креольский
        "ha", // Хауса
        "he", // Иврит
        "hz", // Гереро
        "hi", // Хинди
        "ho", // Хири-моту
        "hr", // Хорватский
        "hu", // Венгерский
        "ig", // Игбо
        "is", // Исландский
        "io", // Идо
        "ii", // Сычуаньский и
        "iu", // Инуктитут
        "ie", // Интерлингве
        "ia", // Интерлингва
        "id", // Индонезийский
        "ik", // Инупиак
        "it", // Итальянский
        "jv", // Яванский
        "ja", // Японский
        "kl", // Гренландский
        "kn", // Каннада
        "ks", // Кашмири
        "ka", // Грузинский
        "kr", // Канури
        "kk", // Казахский
        "km", // Кхмерский
        "ki", // Кикуйю
        "rw", // Киньяруанда
        "ky", // Киргизский
        "kv", // Коми
        "kg", // Конго
        "ko", // Корейский
        "kj", // Куньяма
        "ku", // Курдский
        "lo", // Лаосский
        "la", // Латынь
        "lv", // Латышский
        "li", // Лимбургский
        "ln", // Лингала
        "lt", // Литовский
        "lb", // Люксембургский
        "lu", // Луба-катанга
        "lg", // Ганда
        "mk", // Македонский
        "mh", // Маршалльский
        "ml", // Малаялам
        "mi", // Маори
        "mr", // Маратхи
        "ms", // Малайский
        "mg", // Малагасийский
        "mt", // Мальтийский
        "mn", // Монгольский
        "na", // Науру
        "nv", // Навахо
        "nr", // Южный ндебеле
        "nd", // Северный ндебеле
        "ng", // Ндонга
        "ne", // Непальский
        "nn", // Норвежский (нюнорск)
        "nb", // Норвежский (букмол)
        "no", // Норвежский
        "ny", // Ньянджа
        "oc", // Окситанский
        "oj", // Оджибве
        "or", // Ория
        "om", // Оромо
        "os", // Осетинский
        "pa", // Панджаби
        "pi", // Пали
        "pl", // Польский
        "pt", // Португальский
        "ps", // Пушту
        "qu", // Кечуа
        "rm", // Ретороманский
        "ro", // Румынский
        "rn", // Рунди
        "ru", // Русский
        "sg", // Санго
        "sa", // Санскрит
        "si", // Сингальский
        "sk", // Словацкий
        "sl", // Словенский
        "se", // Северносаамский
        "sm", // Самоанский
        "sn", // Шона
        "sd", // Синдхи
        "so", // Сомали
        "st", // Сесото
        "es", // Испанский
        "sc", // Сардинский
        "sr", // Сербский
        "ss", // Свази
        "su", // Сунданский
        "sw", // Свахили
        "sv", // Шведский
        "ty", // Таитянский
        "ta", // Тамильский
        "tt", // Татарский
        "te", // Телугу
        "tg", // Таджикский
        "tl", // Тагальский
        "th", // Тайский
        "bo", // Тибетский
        "ti", // Тигринья
        "to", // Тонганский
        "tn", // Тсвана
        "ts", // Тсонга
        "tk", // Туркменский
        "tr", // Турецкий
        "tw", // Тви
        "ug", // Уйгурский
        "uk", // Украинский
        "ur", // Урду
        "uz", // Узбекский
        "ve", // Венда
        "vi", // Вьетнамский
        "vo", // Волапюк
        "wa", // Валлонский
        "wo", // Волоф
        "xh", // Косанский
        "yi", // Идиш
        "yo", // Йоруба
        "za", // Чжуань
        "zu", // Зулу
    ];

    public function translateModule() {}

    public function translate(string $text, $language = '')
    {
        if ($this->isLanguageCodePresent($language)) {
            $transText = GoogleTranslate::trans($text, $language, null, ['verify' => $this->isSSLAvailable()], null, true);
            $transText = preg_replace('/:(\w+)/', '$1', $transText);
            return $transText;
        }

        return $text;
    }

    private function isSSLAvailable() {
        $scheme = parse_url($this->getCurrentUrl(), PHP_URL_SCHEME);
        $host = parse_url($this->getCurrentUrl(), PHP_URL_HOST);

        if ($scheme !== 'https') {
            return false;
        }

        $stream = @stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
        $connection = @stream_socket_client(
            "ssl://$host:443",
            $errno,
            $errstr,
            5,
            STREAM_CLIENT_CONNECT,
            $stream
        );
    
        return $connection !== false;
    }

    private function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
    
        return "$protocol://$host$uri";
    }

    private function isLanguageCodePresent($code)
    {
        if (!empty($code)) {
            return in_array(strtolower($code), array_map('strtolower', $this->language_codes));
        }

        return null;
    }
}
