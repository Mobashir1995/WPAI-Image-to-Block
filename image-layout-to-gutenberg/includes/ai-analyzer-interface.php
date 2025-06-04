<?php
// image-layout-to-gutenberg/includes/ai-analyzer-interface.php

if ( ! defined( "WPINC" ) ) {
    die;
}

/**
 * Interface ImageLayoutAIAnalyzerInterface
 *
 * Defines the contract for AI services that analyze an image layout
 * and return a structured representation suitable for Gutenberg block creation.
 */
interface ImageLayoutAIAnalyzerInterface {

    /**
     * Analyzes an image and returns a structured representation of its layout.
     *
     * The implementing class will handle fetching the image data using the ID,
     * sending it to an AI service, and parsing the response into the defined structure.
     *
     * @param int $image_id The WordPress attachment ID of the image to analyze.
     * @return array An array of associative arrays, where each inner array represents a
     *               detected element or block. The structure should be standardized
     *               to facilitate conversion into Gutenberg blocks.
     *               Example structure:
     *               [
     *                   [
     *                       "type" => "core/heading", // Gutenberg block name
     *                       "attributes" => [
     *                           "level" => 2,
     *                           "content" => "This is a Sample Heading"
     *                       ]
     *                   ],
     *                   [
     *                       "type" => "core/paragraph",
     *                       "attributes" => [
     *                           "content" => "This is a sample paragraph detected from the image."
     *                       ]
     *                   ],
     *                   [
     *                       "type" => "core/image",
     *                       "attributes" => [
     *                           "id" => 123, // Attachment ID of a detected image element
     *                           "url" => "http://example.com/path/to/image.jpg",
     *                           "alt" => "Sample alt text"
     *                           // Potentially size, alignment etc.
     *                       ]
     *                   ]
     *                   // More elements can follow, including nested structures like columns if the AI supports it.
     *                   // For columns:
     *                   // [
     *                   //     "type" => "core/columns",
     *                   //     "innerBlocks" => [ // Array of column blocks
     *                   //         [
     *                   //             "type" => "core/column",
     *                   //             "innerBlocks" => [ /* blocks within this column */ ]
     *                   //         ],
     *                   //         [
     *                   //             "type" => "core/column",
     *                   //             "innerBlocks" => [ /* blocks within this column */ ]
     *                   //         ]
     *                   //     ]
     *                   // ]
     *               ]
     * @throws Exception If the analysis fails or an error occurs.
     */
    public function analyzeImage(int $image_id): array;
}
