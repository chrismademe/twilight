<?php

namespace Twilight;

class Compiler {

    public function compile( array $elements ): string {
        $output = '';
        foreach ( $elements as $element ) {
            $output .= $element->render();
        }
        return $output;
    }

}