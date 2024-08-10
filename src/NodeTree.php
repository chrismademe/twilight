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

    private Directives $directives;
    private array|null $current = [];
    private array $tree = [];
    private array $stack = [];
    private array $self_closing_elements = [];
    private array $hoist = [];
    private array $hoisted_elements = [];

    public function __construct( private array $tokens, private array $options ) {
        $this->directives = $options['directives'] ?? new Directives;
        $this->hoist = $options['hoist'] ?? [];
        $this->self_closing_elements = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr',
        ];
    }

    /**
     * Create a tree structure from the tokens
     *
     * @return array
     */
    public function create() {
        $this->tree = [];
        $this->current = &$this->tree;
        $this->stack = []; // Stack to keep track of parent nodes

        foreach ( $this->tokens as $key => $token ) {
            match( $token['type'] ) {
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
             * The "Children" component is a special component that is used to render
             * the children of a component. It is used internally by the framework
             *
             * We will replace it directly here with a Text node.
             */
            if (
                in_array( $element['type'], ['self-closing-component', 'component'] )
                && $element['name'] === 'Children'
            ) {
                $pieces[] = new Text('{{ children | raw }}');
                continue;
            }

            /**
             * Note
             *
             * for this to work, we'll need to process attributes before anything else
             * so that conditionals and such can be used in the attributes
             */
            if (
                isset($element['name'])
                && ( $element['name'] === 'Component' || $element['name'] === 'Element' )
            ) {
                $is_self_closing = $element['type'] === 'self-closing-component';
                $is_dynamic = isset( $element['attributes'][':is'] );
                $is = $element['attributes'][':is'] ?? $element['attributes']['is'];

                unset( $element['attributes'][':is'], $element['attributes']['is'] );

                // Set element type
                if ( $element['name'] === 'Element' ) {
                    $element['type'] = 'tag';
                    $is_self_closing = false;
                } else {
                    $element['type'] = $is_self_closing ? 'self-closing-component' : 'component';
                }

                if ( $is_dynamic ) {
                    $element['dynamic_name'] = $is;
                } else {
                    $element['name'] = $is;
                }
            }

            /**
             * Ignored components are treated like HTML elements and we will render them as such
             */
            if (
                in_array( $element['type'], ['component', 'self-closing-component'] )
                && array_key_exists( 'ignore', $this->options )
                && in_array( $element['name'], $this->options['ignore'] )
            ) {
                $element['type'] = $element['type'] === 'self-closing-component' ? 'self-closing-tag' : 'tag';

                if ( $element['type'] === 'self-closing-tag' ) {
                    $this->self_closing_elements[] = $element['name'];
                }
            }

            if ( $element['type'] === 'tag' || $element['type'] === 'self-closing-tag' ) {
                $html_element = new HTMLElement(
                    $element['name'],
                    in_array($element['name'], $this->self_closing_elements)
                );
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
                    $children = $this->parse_slots($element, $component);
                    $component->set_children($this->convert($children));
                }

                /**
                 * Hoisted components are pulled out of the tree and returned to the implementor
                 * in an array, these are ideal for things like styles and scripts that need to
                 * be included in the head of the document
                 *
                 * These components are not rendered in the tree
                 */
                if ( in_array( $element['name'], $this->hoist ) ) {
                    $this->hoisted_elements[] = $component;
                    continue;
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
        if ( ! empty($this->stack) ) {
            $this->current = &$this->stack[count($this->stack) - 1];
            array_pop($this->stack);
        }
    }

    /**
     * Get Slots
     *
     * @param array $element
     * @return array
     */
    private function parse_slots( array $element, Component $component ) {
        $slots = array_filter($element['children'], fn($child) => $child['type'] === 'slot');

        if ( empty($slots) ) {
            return $element['children'];
        }

        foreach ( $slots as $slot ) {
            $slot_name = $slot['attributes']['name'];
            $component->set_slot($slot_name, $this->convert($slot['children']));
        }

        $children = array_filter($element['children'], fn($child) => $child['type'] !== 'slot');

        return $children;
    }

    /**
     * Get Hoisted Elements
     *
     * @return array
     */
    public function get_hoisted_elements() {
        return $this->hoisted_elements;
    }

}