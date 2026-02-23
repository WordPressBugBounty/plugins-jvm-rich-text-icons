<?php
/**
 * SVG Sanitizer — Security Base
 *
 * Removes XSS vectors from SVG files uploaded via the icon plugin:
 * - Strips comments and processing instructions
 * - Removes forbidden tags (script, iframe, object, embed, link, style, foreignObject)
 * - Strips editor-specific namespace elements/attributes (Inkscape, Sodipodi, etc.)
 * - Removes metadata (<metadata>, <title>, <desc>)
 * - Removes forbidden attributes (on* event handlers, unsafe hrefs)
 * - Removes external references
 *
 * Used standalone in the free plugin. Extended by SVG_Sanitizer in the pro plugin,
 * which adds color normalization, viewBox normalization, and white-mask conversion.
 *
 * Usage:
 *   $sanitizer = new JVM_RTI_SVG_Sanitizer();
 *   $result    = $sanitizer->process( $raw_svg_string );
 *
 *   if ( $result->has_error() ) {
 *       $error = $result->get_error(); // WP_Error
 *   } else {
 *       $svg = $result->svg;           // clean SVG string
 *   }
 */

if (!defined("ABSPATH")) {
    exit();
}

// ---------------------------------------------------------------------------
// Result object
// ---------------------------------------------------------------------------

class JVM_RTI_SVG_Sanitizer_Result
{
    public string $svg = "";

    private ?\WP_Error $error = null;

    public static function from_error(\WP_Error $error): static
    {
        $result = new static();
        $result->error = $error;
        return $result;
    }

    public function has_error(): bool
    {
        return $this->error !== null;
    }

    public function get_error(): ?\WP_Error
    {
        return $this->error;
    }
}

// ---------------------------------------------------------------------------
// Base sanitizer (security only)
// ---------------------------------------------------------------------------

class JVM_RTI_SVG_Sanitizer
{
    /** Tags that are outright forbidden (security). */
    protected array $forbidden_tags = ["script", "iframe", "object", "embed", "link", "style", "foreignObject"];

    /** Attributes that are forbidden on any element (security). */
    protected array $forbidden_attributes = [
        "onload",
        "onclick",
        "onmouseover",
        "onmouseout",
        "onmousemove",
        "onfocus",
        "onblur",
        "onchange",
        "oninput",
        "onsubmit",
        "onkeydown",
        "onkeyup",
        "onkeypress",
        "onerror",
        "onabort",
        "onactivate",
        "onbegin",
        "onend",
        "onrepeat",
        "onzoom",
        "formaction",
        "href",
    ];

    /** Namespaces added by editors that we want to strip. */
    protected array $strip_namespaces = ["sodipodi", "inkscape", "dc", "cc", "rdf", "xodm"];

    /** Options (base has none; subclasses may extend). */
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Process a raw SVG string: run all security steps.
     *
     * @param  string $raw Raw SVG markup.
     * @return JVM_RTI_SVG_Sanitizer_Result
     */
    public function process(string $raw): JVM_RTI_SVG_Sanitizer_Result
    {
        $raw = trim($raw);

        if (empty($raw)) {
            return JVM_RTI_SVG_Sanitizer_Result::from_error(
                new \WP_Error("empty_svg", __("SVG input is empty.", "jvm-rich-text-icons"))
            );
        }

        $dom = $this->parse($raw);
        if (is_wp_error($dom)) {
            return JVM_RTI_SVG_Sanitizer_Result::from_error($dom);
        }

        $svg = $dom->documentElement;

        if (strtolower($svg->nodeName) !== "svg") {
            return JVM_RTI_SVG_Sanitizer_Result::from_error(
                new \WP_Error("invalid_svg", __("Root element is not <svg>.", "jvm-rich-text-icons"))
            );
        }

        // Security — must run first.
        $this->remove_comments($dom);
        $this->remove_processing_instructions($dom);
        $this->remove_forbidden_tags($dom);
        $this->remove_namespace_elements($dom);
        $this->remove_metadata($dom);
        $this->remove_forbidden_attributes($dom);
        $this->remove_external_references($dom);

        $result = new JVM_RTI_SVG_Sanitizer_Result();
        $result->svg = $this->serialize($dom);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Parsing & serialization
    // -------------------------------------------------------------------------

    protected function parse(string $raw): \DOMDocument|\WP_Error
    {
        $prev_use_errors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // LIBXML_NONET prevents external network requests during parse.
        $loaded = $dom->loadXML($raw, LIBXML_NONET | LIBXML_NOBLANKS);

        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($prev_use_errors);

        if (!$loaded) {
            $message = !empty($errors) ? trim($errors[0]->message) : "Unknown XML parse error";
            return new \WP_Error("parse_error", $message);
        }

        return $dom;
    }

    protected function serialize(\DOMDocument $dom): string
    {
        $output = $dom->saveXML($dom->documentElement);
        $output = preg_replace("/^<\?xml[^?]*\?>\s*/i", "", $output);
        return trim($output);
    }

    // -------------------------------------------------------------------------
    // Security cleaning
    // -------------------------------------------------------------------------

    protected function remove_comments(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $comments = $xpath->query("//comment()");
        foreach (iterator_to_array($comments) as $comment) {
            $comment->parentNode?->removeChild($comment);
        }
    }

    protected function remove_processing_instructions(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $pis = $xpath->query("//processing-instruction()");
        foreach (iterator_to_array($pis) as $pi) {
            $pi->parentNode?->removeChild($pi);
        }
    }

    protected function remove_forbidden_tags(\DOMDocument $dom): void
    {
        foreach ($this->forbidden_tags as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    protected function remove_namespace_elements(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        foreach ($this->strip_namespaces as $ns) {
            $nodes = $xpath->query("//*[starts-with(name(), '{$ns}:')]");
            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
        $this->remove_namespace_attributes($dom->documentElement);
    }

    protected function remove_namespace_attributes(\DOMElement $element): void
    {
        $attrs_to_remove = [];
        foreach ($element->attributes as $attr) {
            $name = $attr->nodeName;
            foreach ($this->strip_namespaces as $ns) {
                if ($name === "xmlns:{$ns}" || str_starts_with($name, "{$ns}:")) {
                    $attrs_to_remove[] = $name;
                }
            }
        }
        foreach ($attrs_to_remove as $attr_name) {
            $element->removeAttribute($attr_name);
        }
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $this->remove_namespace_attributes($child);
            }
        }
    }

    protected function remove_metadata(\DOMDocument $dom): void
    {
        foreach (["metadata", "title", "desc"] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    protected function remove_forbidden_attributes(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $all = $xpath->query("//*");

        foreach ($all as $element) {
            if (!($element instanceof \DOMElement)) {
                continue;
            }

            $to_remove = [];

            foreach ($element->attributes as $attr) {
                $name = strtolower($attr->nodeName);

                if (str_starts_with($name, "on")) {
                    $to_remove[] = $attr->nodeName;
                    continue;
                }

                if (in_array($name, $this->forbidden_attributes, true)) {
                    $tag = strtolower($element->nodeName);
                    if ($name === "href" && in_array($tag, ["a", "use", "image"], true)) {
                        if (!$this->is_safe_href($attr->value)) {
                            $to_remove[] = $attr->nodeName;
                        }
                        continue;
                    }
                    $to_remove[] = $attr->nodeName;
                }
            }

            foreach ($to_remove as $attr_name) {
                $element->removeAttribute($attr_name);
            }
        }
    }

    protected function is_safe_href(string $value): bool
    {
        $value = trim(strtolower($value));
        return !str_starts_with($value, "javascript:") && !str_starts_with($value, "data:");
    }

    protected function remove_external_references(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $all = $xpath->query("//*");

        foreach ($all as $element) {
            if (!($element instanceof \DOMElement)) {
                continue;
            }

            $attrs_to_remove = [];
            foreach ($element->attributes as $attr) {
                // Match href in any namespace (xlink:href) and plain href.
                if ($attr->localName === "href") {
                    $val = $attr->value;
                    // Remove if: JavaScript/data URI (XSS), or external URL (SSRF/resource load).
                    // Safe values start with "#" (internal reference) or are empty.
                    if (!$this->is_safe_href($val) || (!str_starts_with(trim($val), "#") && trim($val) !== "")) {
                        $tag = strtolower($element->nodeName);
                        // For <a>, allow any safe URL. For all other elements, only allow internal refs.
                        if ($tag !== "a" || !$this->is_safe_href($val)) {
                            $attrs_to_remove[] = $attr;
                        }
                    }
                }
            }
            foreach ($attrs_to_remove as $attr) {
                $element->removeAttributeNode($attr);
            }
        }

        // Remove <image> elements that point to external resources.
        $images = $dom->getElementsByTagName("image");
        foreach (iterator_to_array($images) as $img) {
            $href = $img->getAttribute("href") ?: $img->getAttributeNS("http://www.w3.org/1999/xlink", "href");
            if ($href && !str_starts_with(trim($href), "#")) {
                $img->parentNode?->removeChild($img);
            }
        }
    }
}
