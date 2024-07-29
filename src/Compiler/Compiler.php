<?php

namespace Twilight\Compiler;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twilight\NodeTree;
use Twilight\Renderer;
use Twilight\Tokenizer;

class Compiler {

    private array $hoisted = [];
    private $timer;

    public function __construct( private array $options = [] ) {
        $this->timer = microtime(true);
    }

    /**
     * Compile
     *
     * Compile all files in the input directory and write them to the output directory.
     *
     * @return array
     */
    public function compile(): array {
        $this->clear_dist();

        $files_to_compile = $this->get_views();

        if ( empty( $files_to_compile ) ) {
            return [
                'time_taken' => number_format( microtime(true) - $this->timer, 4 ),
                'file_count' => 0
            ];
        }

        $relative_file_paths = array_map( fn( $file ) => $this->get_relative_path( $file ), $files_to_compile );

        foreach ( $relative_file_paths as $file ) {
            $output = $this->compile_file( $file );
            $this->write_file( $file, $output );
        }

        $files_compiled = count( $relative_file_paths );

        return [
            'time_taken' => number_format( microtime(true) - $this->timer, 4 ),
            'file_count' => $files_compiled,
            'files' => $relative_file_paths
        ];
    }

    /**
     * Compile File
     *
     * @param string $file
     * @return string
     */
    private function compile_file( string $file ): string {
        $input = file_get_contents( $this->options['input'] . '/' . $file );

        $renderer = new Renderer();
        $tokenizer = new Tokenizer( $input );
        $tree = new NodeTree( $tokenizer->tokenize(), [
            'directives' => $this->options['directives'],
            'ignore' => $this->options['ignore'],
            'hoist' => $this->options['hoist'] ?? []
        ] );

        $elements = $tree->create();

        $this->hoisted[ $file ][] = $tree->get_hoisted_elements();

        return $renderer->render( $elements );
    }

    /**
     * Write File
     *
     * Write the file to the output directory. If the output directory does not exist, create it.
     *
     * @param string $file
     */
    private function write_file( string $file, string $output ) {
        $output_directory = $this->options['output'] . '/' . dirname( $file );
        if ( ! is_dir( $output_directory ) ) {
            mkdir( $output_directory, 0755, true );
        }
        file_put_contents( $this->options['output'] . '/' . $file, $output );
    }

    /**
     * Get Views
     *
     * Scans the input directory for files to compile.
     *
     * @return array
     */
    private function get_views(): array {
        $views = [];
        $directory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->options['input'] ) );
        foreach ( $directory as $file ) {
            if ( $file->isFile() && $file->getExtension() === 'twig' ) {
                $views[] = $file->getPathname();
            }
        }
        return $views;
    }

    /**
     * Get Relative Path
     *
     * Remove the full input path from the file path.
     *
     * @param string $file
     * @return string
     */
    private function get_relative_path( string $file ): string {
        return str_replace( $this->options['input'] . '/', '', $file );
    }

    /**
     * Clear Dist Directory
     *
     * Clear the output directory before compiling.
     * Resusively delete all files and directories in the output directory.
     *
     * @return void
     */
    public function clear_dist() {
        $directory = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $this->options['output'], RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ( $directory as $file ) {
            if ( $file->isDir() ) {
                rmdir( $file->getPathname() );
            } else {
                unlink( $file->getPathname() );
            }
        }
    }

    /**
     * Get Hoisted Elements
     *
     * @return array
     */
    public function get_hoisted_elements(): array {
        return $this->hoisted;
    }

}