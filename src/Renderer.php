<?php

namespace Twilight;

class Renderer {

    public function render( array $elements ): string {
        $output = '';
        foreach ( $elements as $element ) {
            $output .= $element->render();
        }
        return $output;
    }

}