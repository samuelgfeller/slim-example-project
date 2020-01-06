<?php


namespace App\Domain\Validation;


class OutputEscapeService
{

    /**
     * Output escape given value
     *
     * @param $value
     * @return string
     */
    public function escapeValue($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * Output escape values in an array
     *
     * @param array $array
     * @return array
     */
    public function escapeOneDimensionalArray(array $array)
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
     * @param array $twoDArr
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