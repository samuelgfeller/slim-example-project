<?php
/**
 * @var string $severityCssClassName 'error' fatal errors or 'warning' for notices and warnings
 * @var int|null $statusCode http status code e.g. 404
 * @var string|null $reasonPhrase http reason phrase e.g. 'Not Found'
 * @var string|null $exceptionClassName e.g. 'HttpNotFoundException'
 * @var string|null $exceptionMessage e.g. 'Page not found.'
 * @var string|null $pathToMainErrorFile e.g. 'src\Application\Action\'
 * @var string|null $mainErrorFile e.g. 'UserAction.php'
 * @var int|null $errorLineNumber e.g. 123
 * @var array $traceEntries contains keys 'args' (function arguments),
 * 'nonVendorClass' (empty or 'non-vendor' to indicate that string should be highlighted),
 * 'nonVendorFunctionCallClass' (empty or 'non-vendor' to indicate that string should be highlighted),
 * 'classAndFunction' (class and function that was called in stack trace entry),
 * 'fileName' (name of the file in the stack trace entry), 'line' (line number)
 */

// Remove layout if there was a default
$this->setLayout('');
// echo $errorMessage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/error/error-details.css">
    <title>Error</title>
</head>

<body class="<?= $severityCssClassName ?>">
<div id="title-div" class="<?= $severityCssClassName ?>">
    <p><span><?= $statusCode ?> | <?= $reasonPhrase ?></span>
        <span id="exception-name"><?= $exceptionClassName ?></span>
    </p>
    <h1><?= $exceptionMessage ?> in <span id="first-path-chunk"><?= $pathToMainErrorFile ?></span>
        <?= $mainErrorFile ?>
        on line <?= $errorLineNumber ?>.
    </h1>
</div>
<div id="trace-div" class="<?= $severityCssClassName ?>">
    <table>
        <tr class="non-vendor">
            <th id="num-th">#</th>
            <th>Function</th>
            <th>Location</th>
        </tr>
        <?php
        foreach ($traceEntries as $key => $entry) { ?>
            <tr>
                <td class="<?= $entry['nonVendorClass'] ?>"><?= $key ?></td>
                <td class="function-td <?= $entry['nonVendorFunctionCallClass'] ?>">
                    <?= $entry['classAndFunction'] ?>(
                    <?php
                    foreach ($entry['args'] as $argument) { ?>
                        <span class="args-span" data-full-details="<?= $argument['detailed'] ?>">
                            <?= $argument['formatted'] ?></span>,
                        <?php
                    } ?>
                    )
                </td>
                <td class="stack-trace-file-name <?= $entry['nonVendorClass'] ?>">
                    <?= $entry['fileName'] ?>:<span class="lineSpan"><?= $entry['line'] ?></span>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
<script src="assets/error/error-details.js"></script>
</body>
</html>


