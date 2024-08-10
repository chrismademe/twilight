<?php

namespace Twilight;

class Tokenizer {

    private $tokens = [];
    private $position = 0;
    private $length;

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
            'text' => '/^([^<]+)/s'
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
                        break;
                    case 'end-tag':
                        $token = [
                            'type' => 'end-tag',
                            'value' => $matches[0],
                            'name' => $matches[1]
                        ];
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

                // Check for double quoted, single quoted, curly brace or unquoted attribute values
                $value = $attr[3] ?? $attr[4] ?? $attr[5] ?? $attr[6] ?? true;

                // If the attribute value is null, remove it
                if ($value === "null") continue;

                $attributes[ $attr[1] ] = $value;
            }
        }

        return $attributes;
    }

}
