import {fetchData} from "./fetch-data.js?v=0.4.0";

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
    return fetchData(`translate?${params.toString()}`).then(responseJSON => {
        return responseJSON;
    }).catch();
}
