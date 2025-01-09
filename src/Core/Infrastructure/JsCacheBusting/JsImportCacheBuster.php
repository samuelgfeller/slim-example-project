<?php

namespace App\Core\Infrastructure\JsCacheBusting;

use App\Core\Infrastructure\Settings\Settings;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Adds version number to js imports to break cache on version change.
 */
final class JsImportCacheBuster
{
    private ?string $version;
    private string $assetPath;

    public function __construct(Settings $settings)
    {
        $deploymentSettings = $settings->get('deployment');
        $this->version = $deploymentSettings['version'];
        $this->assetPath = $deploymentSettings['asset_path'];
    }

    /**
     * All js files inside the given directory that contain ES6 imports
     * are modified so that the imports have the version number at the
     * end of the file name as query parameters to break cache on
     * version change.
     * This function is called in PhpViewMiddleware only on dev env.
     * Performance wise, this function takes between 10 and 20ms when content
     * is unchanged and between 30 and 50ms when content is replaced.
     *
     * @return void
     */
    public function addVersionToJsImports(): void
    {
        // $start = hrtime(true);
        if (is_dir($this->assetPath)) {
            $rii = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->assetPath, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($rii as $file) {
                $fileInfo = pathinfo($file->getPathname());

                if (isset($fileInfo['extension']) && $fileInfo['extension'] === 'js') {
                    $content = file_get_contents($file->getPathname()) ?: '';
                    $originalContent = $content;
                    // Matches lines that have 'import ' then any string then ' from ' and single or double quote opening then
                    // any string (path) then '.js' and optionally v GET param '?v=234' and '";' at the end with single or double quotes
                    preg_match_all('/import (.|\n|\r|\t)*? from ("|\')(.*?)\.js(\?v=.*?)?("|\');/', $content, $matches);
                    // $matches is an array that contains all matches. In this case, the content is the following:
                    // Key [0] is the entire matching string including the search
                    // Key [1] first variable unknown string after the 'import ' word (e.g. '{requestDropdownOptions}', '{createModal}')
                    // Key [2] single or double quotes of path opening after "from"
                    // Key [3] variable unknown string after the opening single or double quotes after from (only path) e.g.
                    // '../general/js/requestUtil/fail-handler'
                    // Key [4] optional '?v=2' GET param and [5] closing quotes
                    // Loop over import paths
                    foreach ($matches[3] as $key => $importPath) {
                        $oldFullImport = $matches[0][$key];
                        // Remove query params if version is null
                        if ($this->version === null) {
                            $newImportPath = $importPath . '.js';
                        } else {
                            $newImportPath = $importPath . '.js?v=' . $this->version;
                        }
                        // Old import path potentially with GET param
                        $existingImportPath = $importPath . '.js' . $matches[4][$key];
                        // Search for old import path and replace with new one
                        $newFullImport = str_replace($existingImportPath, $newImportPath, $oldFullImport);
                        // Replace in file content
                        $content = str_replace($oldFullImport, $newFullImport, $content);
                    }
                    // Replace file contents with modified one if there are changes
                    if ($originalContent !== $content) {
                        file_put_contents($file->getPathname(), $content);
                    }
                }
            }
        }
        // Divided by a million gets milliseconds and a billion (+9) seconds
        // var_dump('Time used: ' . (hrtime(true) - $start) / 1e+6 . ' ms');
    }
}
