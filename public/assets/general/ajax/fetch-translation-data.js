import {fetchData} from "./fetch-data.js?v=0.3.1";

/**
 * Fetch serverside translation for given words
 *
 * @param {string[]}wordsToTranslate
 * @return Promise<JSON>
 */
export function fetchTranslations(wordsToTranslate) {
    const params = new URLSearchParams();
    wordsToTranslate.forEach((value) => {
        params.append('strings[]', value);
    });
    let translatedWords;
    return fetchData(`translate?${params.toString()}`).then(responseJSON => {
        return responseJSON;
    }).catch();
}
