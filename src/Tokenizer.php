<?php

namespace Twilight;

class Tokenizer {

    private $tree = [];
    private $tokens = [];

    public function __construct( private string $input ) {}

    /**
     * Tokenize the input HTML into an array of tokens
     */
    public function tokenize() {

        $this->input = trim($this->input);

        while( strlen($this->input) > 0 ) {
            $this->match_self_closing_html_tag();
            $this->match_self_closing_component_tag();
            $this->match_opening_html_tag();
            $this->match_opening_component_tag();
            $this->match_end_tag();
            $this->match_html_comment();
            $this->match_twig_comment();
            $this->match_text();
        }

        return $this->tokens;

    }

    /**
     * Match Opening Tags
     */
    private function match_self_closing_html_tag() {
        $did_match = preg_match( '/^<([a-z-]+)([^>]*)\/>/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => 'self-closing-tag',
            'self_closing' => true,
            'name' => $matches[1],
            'value' => $matches[0],
            'attributes' => isset( $matches[2] ) ? $this->parse_attributes( $matches[2] ) : null
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_self_closing_component_tag() {
        $did_match = preg_match( '/^<([A-Z][a-zA-Z-]+)([^>]*)\/>/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => $matches[1] === 'Slot' ? 'self-closing-slot' : 'self-closing-component',
            'self_closing' => true,
            'name' => $matches[1],
            'value' => $matches[0],
            'attributes' => isset($matches[2]) ? $this->parse_attributes($matches[2]) : null
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_opening_html_tag() {
        $did_match = preg_match('/^<([a-z-]+)([^>]*)>/s', $this->input, $matches);

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => 'tag',
            'name' => $matches[1],
            'value' => $matches[0],
            'attributes' => isset($matches[2]) ? $this->parse_attributes($matches[2]) : null
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_opening_component_tag() {
        $did_match = preg_match( '/^<([A-Z][a-zA-Z-]+)([^>]*)>/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => $matches[1] === 'Slot' ? 'slot' : 'component',
            'name' => $matches[1],
            'value' => $matches[0],
            'attributes' => isset($matches[2]) ? $this->parse_attributes($matches[2]) : null
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_end_tag() {
        $did_match = preg_match( '/^<\/([a-zA-Z-]+)>/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => 'end-tag',
            'value' => $matches[0],
            'name' => $matches[1]
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_html_comment() {
        $did_match = preg_match( '/^<!--(.*?)-->/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => 'html-comment',
            'value' => $matches[0]
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_twig_comment() {
        $did_match = preg_match( '/^{#(.*?)(#})/s', $this->input, $matches );

        if ( ! $did_match ) return;

        $this->tokens[] = [
            'type' => 'twig-comment',
            'value' => $matches[0]
        ];

        $this->input = substr( $this->input, strlen($matches[0]) );
    }

    private function match_text() {
        $did_match = preg_match('/^([^<]+)/s', $this->input, $matches );

        if ( ! $did_match ) return;

        // Skip empty strings
        if ( trim($matches[0]) !== '' ) {
            $this->tokens[] = [
                'type' => 'text',
                'value' => $matches[0]
            ];
        }

        $this->input = substr($this->input, strlen($matches[0]));
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
			preg_match_all( '/\s*([a-zA-Z0-9-_:.@-]+)\s*(?:=\s*(?:"([^"]*)"|{([^}]*)}))?/', $string, $matches, PREG_SET_ORDER );

			/**
			 * Push each attribute into the $attributes array
			 * If an attribute has no value, set it to true
			 */
			foreach ( $matches as $attr ) {

				// If the attribute has a value, set it to that, otherwise set it to true
				$value = $attr[2] ?? true;
				$attributes[ $attr[1] ] = $value;

			}
		}

		return $attributes;
	}

}