<?php

namespace Twilight;

use Twilight\Directives;
use Twilight\Nodes\Attributes;
use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLComment;
use Twilight\Nodes\HTMLElement;
use Twilight\Nodes\Text;
use Twilight\Nodes\TwigComment;

class NodeTree {

    private array|null $current = [];
    private array $tree = [];
    private array $stack = [];

    public function __construct( private array $tokens, private Directives $directives ) {}

    /**
     * Create a tree structure from the tokens
     *
     * @return array
     */
    public function create() {
        $this->tree = [];
        $this->current = &$this->tree;
        $this->stack = []; // Stack to keep track of parent nodes

        foreach ($this->tokens as $key => $token) {
            match($token['type']) {
                'tag', 'component', 'slot' => $this->create_opening_tag($token),
                'self-closing-tag', 'self-closing-component', 'self-closing-slot' => $this->create_self_closing_tag($token),
                'text' => $this->create_text_node($token),
                'html-comment' => $this->create_html_comment_node($token),
                'twig-comment' => $this->create_twig_comment_node($token),
                'end-tag' => $this->handle_closing_tag($token),
            };

            unset($this->tokens[$key]);
        }

        return $this->convert($this->tree);
    }

    /**
     * Convert Tree to Twilight Nodes
     */
    private function convert( array $elements ) {
        $pieces = [];

        foreach ( $elements as $element ) {

            /**
             * Note
             *
             * for this to work, we'll need to process attributes before anything else
             * so that conditionals and such can be used in the attributes
             */
            if ( isset($element['name']) && ( $element['name'] === 'Component' || $element['name'] === 'Element' ) ) {
                $is_self_closing = $element['type'] === 'self-closing-component';
                $is_dynamic = isset( $element['attributes'][':is'] );
                $is = $element['attributes'][':is'] ?? $element['attributes']['is'];

                // Set element type
                if ( $element['name'] === 'Element' ) {
                    $element['type'] = $is_self_closing ? 'self-closing-tag' : 'tag';
                } else {
                    $element['type'] = $is_self_closing ? 'self-closing-component' : 'component';
                }

                if ( $is_dynamic ) {
                    $element['dynamic_name'] = $is;
                } else {
                    $element['name'] = $is;
                }
            }

            if ( $element['type'] === 'tag' || $element['type'] === 'self-closing-tag' ) {
                $html_element = new HTMLElement($element['name']);
                $html_element->set_attributes($element['attributes']);
                $html_element->set_directives($this->directives);

                if ( isset( $element['dynamic_name'] ) ) {
                    $html_element->set_dynamic_name($element['dynamic_name']);
                }

                if ( isset( $element['children'] ) ) {
                    $html_element->set_children($this->convert($element['children']));
                }

                $pieces[] = $html_element;
                continue;
            }

            if ( in_array( $element['type'], ['component', 'self-closing-component', 'slot', 'self-closing-slot']) ) {
                $component = new Component($element['name']);
                $component->set_attributes($element['attributes']);
                $component->set_directives($this->directives);

                if ( isset( $element['dynamic_name'] ) ) {
                    $component->set_dynamic_name($element['dynamic_name']);
                }

                if ( isset( $element['children'] ) ) {
                    $component->set_children($this->convert($element['children']));
                }

                $pieces[] = $component;
                continue;
            }

            if ( $element['type'] === 'text' ) {
                $pieces[] = new Text($element['value']);
            }

            if ( $element['type'] === 'html-comment' ) {
                $pieces[] = new HTMLComment($element['value']);
            }

            if ( $element['type'] === 'twig-comment' ) {
                $pieces[] = new TwigComment($element['value']);
            }
        }

        return $pieces;
    }

    /**
     * Create an opening tag node
     *
     * @param array $token
     */
    private function create_opening_tag( array $token ) {
        // Push the current node to the stack
        $this->stack[] = &$this->current;

        // Add the new node as a child
        $this->current[] = [
            'name' => $token['name'],
            'type' => $token['type'],
            'value' => $token['value'],
            'attributes' => $token['attributes'],
            'children' => []
        ];

        // Move to the new node's children
        $this->current = &$this->current[count($this->current) - 1]['children'];
    }

    /**
     * Create a self-closing tag node
     *
     * @param array $token
     */
    private function create_self_closing_tag( array $token ) {
        $this->current[] = [
            'name' => $token['name'],
            'type' => $token['type'],
            'value' => $token['value'],
            'attributes' => $token['attributes'],
        ];
    }

    /**
     * Create a text node
     *
     * @param array $token
     */
    private function create_text_node( array $token ) {
        $this->current[] = [
            'type' => 'text',
            'value' => $token['value']
        ];
    }

    /**
     * Create an HTML comment node
     *
     * @param array $token
     */
    private function create_html_comment_node( array $token ) {
        $this->current[] = [
            'type' => 'html-comment',
            'value' => $token['value']
        ];
    }

    /**
     * Create a Twig comment node
     *
     * @param array $token
     */
    private function create_twig_comment_node( array $token ) {
        $this->current[] = [
            'type' => 'twig-comment',
            'value' => $token['value']
        ];
    }

    /**
     * Handle closing tags
     *
     * When a closing tag is encountered, pop the last node from the stack
     *
     * TODO Add a check in case we encounter an unexpected closing tag
     */
    private function handle_closing_tag( array $token ) {
        if (!empty($this->stack)) {
            $this->current = &$this->stack[count($this->stack) - 1];
            array_pop($this->stack);
        }
    }

}