<?php


namespace App\Domain\Utility;

/**
 * Twig escapes automatically but if the data is requested via ajax or if a PHP-Renderer like
 * slimphp/PHP-View is used, escaping should be manually done
 *
 * Class OutputEscapeService
 * @package App\Domain\Validation
 */
class OutputEscaper
{

    /**
     * Output escape given value
     *
     * @param $value
     * @return string
     */
    public function escapeValue($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Output escape values in an array
     *
     * @param string[] $array
     * @return array
     */
    public function escapeOneDimensionalArray(array $array): array
    {
        $escapedArr = [];
        foreach ($array as $key => $value){
            $escapedArr[$key] = $this->escapeValue($value);
        }
        return $escapedArr;
    }

    /**
     * Output escape all values of a two dimensional array
     *
     * @param array[] $twoDArr
     * @return array
     */
    public function escapeTwoDimensionalArray(array $twoDArr)
    {
        $escapedArr = [];
        foreach ($twoDArr as $key => $array){
            $escapedArr[$key] = $this->escapeOneDimensionalArray($array);
        }
        return $escapedArr;
    }

    /**
     * Output escape all values of a three dimensional array
     *
     * @param array $threeDArr
     * @return array
     */
    public function escapeThreeDimensionalArray(array $threeDArr)
    {
        $escapedArr = [];
        foreach ($threeDArr as $key => $twoDArr){
            $escapedArr[$key] = $this->escapeTwoDimensionalArray($twoDArr);
        }
        return $escapedArr;
    }


}