<?php

namespace Twilight;

class Tokenizer {

    private $tokens = [];
    private $position = 0;
    private $length;
    private $is_inside_raw_content = false;

    public function __construct( private string $input, private array $options = [] ) {
        $this->input = trim($input);
        $this->length = strlen($this->input);
    }

    /**
     * Tokenize the input HTML into an array of tokens
     */
    public function tokenize() {
        while ( $this->position < $this->length ) {
            if ( $this->match_token() ) {
                continue;
            }
            // Move to the next character to avoid infinite loops
            $this->position++;
        }
        return $this->tokens;
    }

    /**
     * Match all token types in a single regex
     */
    private function match_token() {
        $patterns = [
            'doctype' => '/^<!(.*?)>/s',
            'self-closing-component' => '/^<([A-Z][a-zA-Z0-9.-]*)([^>]*)\/>/s',
            'self-closing-tag' => '/^<([a-z][a-zA-Z0-9-]*)([^>]*)\/>/s',
            'component' => '/^<([A-Z][a-zA-Z0-9.-]*)([^>]*)>/s',
            'tag' => '/^<([a-z][a-zA-Z0-9-]*)([^>]*)>/s',
            'end-tag' => '/^<\/([a-zA-Z0-9.-]+)>/s',
            'html-comment' => '/^<!--(.*?)-->/s',
            'twig-comment' => '/^{#(.*?)(#})/s',
            'text' => $this->is_inside_raw_content
                ? '/^(.*?)(?=<\/script>|<\/style>)/is'
                : '/^([^<]+)/s'
        ];

        foreach ( $patterns as $type => $pattern ) {
            if ( preg_match($pattern, substr($this->input, $this->position), $matches) ) {
                switch ( $type ) {
                    case 'self-closing-component':
                    case 'self-closing-tag':
                        $token = [
                            'type' => $matches[1] === 'Slot' ? 'slot' : $type,
                            'self_closing' => true,
                            'name' => $matches[1],
                            'value' => $matches[0],
                            'attributes' => isset($matches[2]) ? $this->parse_attributes($matches[2]) : null
                        ];
                        break;
                    case 'component':
                    case 'tag':
                        $token = [
                            'type' => $matches[1] === 'Slot' ? 'slot' : $type,
                            'name' => $matches[1],
                            'value' => $matches[0],
                            'attributes' => isset($matches[2]) ? $this->parse_attributes($matches[2]) : null
                        ];

                        if ( $matches[1] === 'Script' || $matches[1] === 'Style' ) {
                            $this->is_inside_raw_content = true;
                        }

                        break;
                    case 'end-tag':
                        $token = [
                            'type' => 'end-tag',
                            'value' => $matches[0],
                            'name' => $matches[1]
                        ];

                        if ( $matches[1] === 'Script' || $matches[1] === 'Style' ) {
                            $this->is_inside_raw_content = false;
                        }

                        break;
                    case 'html-comment':
                    case 'twig-comment':
                        $token = [
                            'type' => $type,
                            'value' => $matches[0]
                        ];
                        break;
                    case 'doctype':
                    case 'text':
                        if ( trim($matches[0]) !== '' ) {
                            $token = [
                                'type' => 'text',
                                'value' => $matches[0]
                            ];
                        } else {
                            // Skip over whitespace text
                            $this->position += strlen( $matches[0] );
                            return true;
                        }
                        break;
                }
                $this->tokens[] = $token;
                $this->position += strlen( $matches[0] );
                return true;
            }
        }
        return false;
    }

    /**
     * Parse Attributes
     *
     * @param string $string
     * @return array
     */
    private function parse_attributes( string $string ) {
        $attributes = [];

        if ( ! empty( $string ) ) {
            preg_match_all(
                '/\s*([a-zA-Z0-9-_:.@]+)(\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|\{([^}]*)\}|([^\s>]+)))?/',
                $string,
                $matches,
                PREG_SET_ORDER
            );

            foreach ( $matches as $attr ) {
                /**
                 * Set a flag for empty values
                 *
                 * We use this in other areas of the compile process to make sure we treat
                 * attributes as you would expect in HTML. For example, if an attribute is
                 * empty, we simply render it without a value on an HTML element, but for
                 * components, we pass it in as `true`
                 */
                $attributes[ $attr[1] ] = $attr[3] ?? '__empty__';
            }
        }

        return $attributes;
    }

}
