window.onload = function() {
    let elements = document.querySelectorAll('.stack-trace-file-name');
    elements.forEach(function(element) {
        camelWrap(element);
    });
}

/**
 * This function is used to apply the camelWrapUnicode function to a given DOM node
 * and then replace all zero-width spaces in the node's innerHTML with <wbr> elements.
 * The <wbr> element represents a word break opportunity—a position within text where
 * the browser may optionally break a line, though its line-breaking rules would not
 * otherwise create a break at that location.
 *
 * @param {Node} node - The DOM node to which the camelWrapUnicode function should
 * be applied and in whose innerHTML the zero-width spaces should be replaced
 * with <wbr> elements.
 */
function camelWrap(node) {
    camelWrapUnicode(node);
    node.innerHTML = node.innerHTML.replace(/\u200B/g, "<wbr>");
}

/**
 * This function is used to insert a zero-width space before each uppercase letter in
 * a camelCase string.
 * It does this by recursively traversing the DOM tree starting from the given node.
 * For each text node it finds, it replaces the node's value with a new string where
 * a zero-width space has been inserted before each uppercase letter.
 *
 * Source: http://heap.ch/blog/2016/01/19/camelwrap/
 * @param {Node} node - The node from where to start the DOM traversal.
 */
function camelWrapUnicode(node) {
    // Start from the first child of the given node and continue to the next sibling until there are no more siblings.
    for (node = node.firstChild; node; node = node.nextSibling) {
        // If the current node is a text node, replace its value.
        if (node.nodeType === Node.TEXT_NODE) {
            // Replace the node's value with a new string where a zero-width space has been inserted before each uppercase letter.
            // This is done by first matching any word character or colon that is repeated 18 or more times, and for each match,
            // a new string is returned where a zero-width space has been inserted before each uppercase letter.
            // The same is done by matching any dot character, but without the repetition requirement.
            node.nodeValue = node.nodeValue.replace(/[\w:]{18,}/g, function (str) {
                return str.replace(/([a-z])([A-Z])/g, "$1\u200B$2");
            });
        } else {
            // If the current node is not a text node, continue the traversal from this node.
            camelWrapUnicode(node);
        }
    }
}