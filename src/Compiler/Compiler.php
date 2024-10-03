<?php

namespace Twilight\Compiler;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twilight\Directives;
use Twilight\NodeTree;
use Twilight\Renderer;
use Twilight\Tokenizer;

class Compiler {

    private array $options = [];
    private array $hoisted = [];
    private Filesystem $input;
    private Filesystem $output;
    private Filesystem|null $asset_output = null;
    private $timer;

    public function __construct() {
        $this->timer = microtime(true);
    }

    /**
     * From
     *
     * Set the input directory.
     * @param string $path
     * @return Compiler
     */
    public function from( string $path ): Compiler {
        $this->options['input'] = $path;
        $this->input = new Filesystem( new LocalFilesystemAdapter( $path ) );
        return $this;
    }

    /**
     * To
     *
     * Set the output directory.
     * @param string $path
     * @return Compiler
     */
    public function to( string $path ): Compiler {
        $this->options['output'] = $path;
        $this->output = new Filesystem( new LocalFilesystemAdapter( $path ) );
        return $this;
    }

    /**
     * To
     *
     * Set the output directory.
     * @param string $path
     * @return Compiler
     */
    public function assets_to( string $path ): Compiler {
        $this->options['asset_output'] = $path;
        $this->asset_output = new Filesystem( new LocalFilesystemAdapter( $path ) );
        return $this;
    }

    /**
     * Hoist
     *
     * Hoist elements to the top of the file.
     * @param array $elements
     * @return Compiler
     */
    public function hoist( array $elements ): Compiler {
        $this->options['hoist'] = $elements;
        return $this;
    }

    /**
     * Directives
     */
    public function directives( Directives $directives ): Compiler {
        $this->options['directives'] = $directives;
        return $this;
    }

    /**
     * Ignore
     *
     * Elements not to compile
     *
     * @param array $elements
     * @return Compiler
     */
    public function ignore( array $elements ): Compiler {
        $this->options['ignore'] = $elements;
        return $this;
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
        $input = $this->input->read( $file );

        $renderer = new Renderer();
        $tokenizer = new Tokenizer( $input );
        $tree = new NodeTree( $tokenizer->tokenize(), [
            'directives' => $this->options['directives'],
            'ignore' => $this->options['ignore'],
            'hoist' => $this->options['hoist'] ?? []
        ] );

        $elements = $tree->create();

        $this->hoisted[ $file ] = $tree->get_hoisted_elements();
        $this->compile_assets( $file, $tree->get_hoisted_elements() );

        return $renderer->render( $elements );
    }

    /**
     * Compile Assets
     *
     * Compiles the content is Script and Style elements into their
     * respective files.
     *
     * @param string $parent_file
     * @param array $hoisted_elements
     */
    private function compile_assets( string $parent_file, array $hoisted_elements ) {
        $filename_without_extension = str_replace( '.twig', '', $parent_file );

        /**
         * Remove the '/template' suffix from the filename if it exists.
         * Components usually have this.
         */
        if ( str_ends_with( $filename_without_extension, '/template' ) ) {
            $filename_without_extension = str_replace(  '/template', '', $filename_without_extension );
        }

        foreach ( $hoisted_elements as $element ) {
            if ( $element->name === 'Script' ) {
                $this->write_file(
                    file: $filename_without_extension . '/script.js',
                    output: $element->children[0]->value,
                    type: 'asset'
                );
            }

            if ( $element->name === 'Style' ) {
                $this->write_file(
                    file: $filename_without_extension . '/style.css',
                    output: $element->children[0]->value,
                    type: 'asset'
                );
            }
        }
    }

    /**
     * Write File
     *
     * Write the file to the output directory. If the output directory does not exist, create it.
     *
     * @param string $file
     * @param string $output
     * @param string $type view|asset
     */
    private function write_file( string $file, string $output, string $type = 'view' ) {
        $output_directory = dirname( $file );
        $filesystem = $type === 'view' || $this->asset_output === null
            ? $this->output
            : $this->asset_output;

        if ( ! $filesystem->directoryExists( $output_directory ) ) {
            $filesystem->createDirectory( $output_directory );
        }

        $filesystem->write( $file, $output );
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
        $contents = $this->input->listContents('', true);

        foreach ( $contents as $item ) {
            if ( $item->isFile() && pathinfo( $item->path(), PATHINFO_EXTENSION ) === 'twig' ) {
                $views[] = $item->path();
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
        $contents = $this->output->listContents('', true)
            ->sortByPath()
            ->toArray();

        foreach ( $contents as $item ) {
            if ( $item->isDir() ) {
                if ( $this->output->directoryExists( $item->path() ) ) {
                    $this->output->deleteDirectory( $item->path() );
                }
            } else {
                if ( $this->output->fileExists( $item->path() ) ) {
                    $this->output->delete( $item->path() );
                }
            }
        }

        /**
         * If it's not empty, try again.
         */
        $is_empty = empty(
            $this->output->listContents('', true)
                ->sortByPath()
                ->toArray()
        );

        if ( ! $is_empty ) {
            $this->clear_dist();
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