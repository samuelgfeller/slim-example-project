import {fetchData} from "./fetch-data.js?v=0.4.0";

/**
 * Fetch serverside translation for given words.
 *
 * @param {string[]}wordsToTranslate
 * @return {Promise} Promise object represents either a JSON object with
 * translated words or an array of English words if fetch fails
 */
export function fetchTranslations(wordsToTranslate) {
    const params = new URLSearchParams();
    wordsToTranslate.forEach((value) => {
        params.append('strings[]', value);
    });
    return fetchData(`translate?${params.toString()}`).then(responseJSON => {
        return responseJSON;
    }).catch(err => {
        console.error(err);
        // Return array with english words if fetch fails
        return Object.fromEntries(wordsToTranslate.map(value => [value, value]));
    });
}
