import {fetchData} from "./fetch-data.js";

/**
 * Fetch serverside translation for given words
 *
 * @param {string[]}wordsToTranslate
 * @return {string[]}
 */
export function fetchTranslations(wordsToTranslate){
    const params = new URLSearchParams();
    wordsToTranslate.forEach((value) => {
        params.append('strings[]', value);
    });
    let translatedWords;
    fetchData(`translate?${params.toString()}`).then(responseJSON => {
        translatedWords = responseJSON;
        return translatedWords;
    }).catch();
}
