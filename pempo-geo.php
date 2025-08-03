<?php
// Helper function for truncating text without outputting directly to HTML
function pempo_truncate_text($text, $limit) {
    if (mb_strlen($text, 'UTF-8') <= $limit) return $text;
    $truncated = mb_substr($text, 0, $limit, 'UTF-8');
    $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');
    if ($lastSpace !== false) {
        $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
    }
    return rtrim($truncated) . '...';
}
?>
<?php
/*
Plugin Name: PEMPO GEO
Plugin URI: https://pempo.ai
Description: WordPress plugin for Generative Engine Optimization (GEO) - structured metadata and schema markup to help publishers get found, understood, and cited by AI language models.
Version: 1.0.2
Author: Josh Jaffe
Author URI: https://www.linkedin.com/in/joshuajaffe/
License: GPL v2 or later
Text Domain: wordpress-geo
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ==========================================================
// 1. INITIALIZATION HOOKS AND ACTIONS
// ==========================================================

register_activation_hook(__FILE__, 'pempo_activate');
function pempo_activate() {
    // Nothing to do on activation currently
}

register_deactivation_hook(__FILE__, 'pempo_deactivate');
function pempo_deactivate() {
    // Nothing to do on deactivation currently
}

// Main schema injection hook
add_action('wp_head', 'pempo_inject_schema_to_head');


// ==========================================================
// 2. ADMIN SETTINGS
// ==========================================================

// Add to your plugin - creates simple admin page
add_action('admin_menu', 'pempo_add_admin_menu');

function pempo_add_admin_menu() {
    add_options_page(
        'PEMPO GEO Settings',
        'PEMPO GEO', 
        'manage_options',
        'pempo-settings',
        'pempo_settings_page'
    );
}

function pempo_settings_page() {
    // Handle form submission
    if (isset($_POST['submit'])) {
        check_admin_referer('pempo_settings_nonce');
        
        if (isset($_POST['pempo_citation_style'])) {
            update_option('pempo_citation_style', sanitize_text_field(wp_unslash($_POST['pempo_citation_style'])));
        }
        if (isset($_POST['pempo_source_reliability'])) {
            update_option('pempo_source_reliability', sanitize_text_field(wp_unslash($_POST['pempo_source_reliability'])));
        }
        if (isset($_POST['pempo_publication_name'])) {
            update_option('pempo_publication_name', sanitize_text_field(wp_unslash($_POST['pempo_publication_name'])));
        }
        
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>PEMPO GEO Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('pempo_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Publication Name for Citations</th>
                    <td>
                        <input type="text" name="pempo_publication_name" 
                            value="<?php echo esc_attr(get_option('pempo_publication_name', get_bloginfo('name'))); ?>" 
                            placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>" 
                            class="regular-text" />
                        <p class="description">How your publication should be cited by AI (defaults to site title)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Citation Style</th>
                    <td>
                        <select name="pempo_citation_style">
                            <option value="academic" <?php selected(get_option('pempo_citation_style', 'academic'), 'academic'); ?>>Academic (APA-style)</option>
                            <option value="journalism" <?php selected(get_option('pempo_citation_style', 'academic'), 'journalism'); ?>>Journalism</option>
                            <option value="web" <?php selected(get_option('pempo_citation_style', 'academic'), 'web'); ?>>Web/Blog</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Source Reliability</th>
                    <td>
                        <select name="pempo_source_reliability">
                            <option value="primary" <?php selected(get_option('pempo_source_reliability', 'primary'), 'primary'); ?>>Primary Source</option>
                            <option value="secondary" <?php selected(get_option('pempo_source_reliability', 'primary'), 'secondary'); ?>>Secondary Source</option>
                            <option value="blog" <?php selected(get_option('pempo_source_reliability', 'primary'), 'blog'); ?>>Blog/Opinion</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}



// ==========================================================
// 3. CONTENT FILTERING FUNCTIONS
// ==========================================================

function pempo_get_clean_post_content($post_id) {
    $post = get_post($post_id);
    $content = apply_filters('the_content', $post->post_content);
    
    // Create DOM document to filter widgets
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<!DOCTYPE html><html><body>' . $content . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    // Remove promotional widgets
    $xpath = new DOMXPath($dom);
    
    // Target: .widget, [class*="widget"], iframe[data-test-id]
    $widgets = $xpath->query('//*[@class="widget" or contains(@class, "widget") or self::iframe[@data-test-id]]');
    
    foreach ($widgets as $widget) {
        if ($widget->parentNode) {
            $widget->parentNode->removeChild($widget);
        }
    }
    
    // Get clean content
    $filtered_content = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
    $filtered_content = preg_replace('/<\/?body>/', '', $filtered_content);
    
    return html_entity_decode(wp_strip_all_tags($filtered_content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function pempo_clean_json_output($json_string) {
    $data = json_decode($json_string, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Better fallback - return valid empty schema instead of just '{}'
        return json_encode([
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => "Schema Generation Error"
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ==========================================================
// 4. CORE SCHEMA GENERATION
// ==========================================================

function pempo_generate_schema($post_id) {
    try {
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish') {
            return [];
        }

        if (empty($post->post_content)) {
            return [];
        }

        // Clean and Truncate Content
        $clean_content = pempo_get_clean_post_content($post_id);

        // Use sentence-aware chunking instead of fixed-length splitting
        $sentences = preg_split('/(?<=[.!?])\s+/', $clean_content, -1, PREG_SPLIT_NO_EMPTY);
        $chunk_text = '';
        $chunks = [];
        $chunk_index = 0;
        foreach ($sentences as $sentence) {
            if (mb_strlen($chunk_text . ' ' . $sentence, 'UTF-8') > 1800) {
                $chunks[] = [
                    "@type" => "TextChunk",
                    "text" => trim($chunk_text),
                    "chunkId" => "chunk-$chunk_index"
                ];
                $chunk_index++;
                $chunk_text = $sentence;
            } else {
                $chunk_text .= ' ' . $sentence;
            }
        }
        if (!empty(trim($chunk_text))) {
            $chunks[] = [
                "@type" => "pempo:TextChunk",
                "pempo:text" => trim($chunk_text),
                "pempo:chunkId" => "chunk-$chunk_index"
            ];
        }

        // Truncate for other processing
        $clean_content_for_other = pempo_clean_text($clean_content);
        $clean_excerpt = pempo_clean_text(wp_strip_all_tags(get_the_excerpt($post_id)));
        $capped_content = mb_substr($clean_content_for_other, 0, 25000);

        // Extract main Q&A for schema
        // Use pempo_extract_primary_citation_qa instead of undefined function
        $faqs = pempo_extract_primary_citation_qa($post, $clean_content, $clean_content_for_other);

        $categories = get_the_category($post_id);
        $category_name = (!empty($categories) && isset($categories[0])) ? $categories[0]->name : '';

        // Initialize schema with context and type
        $schema = [
            "@context" => [
                "https://schema.org",
                ["pempo" => "https://pempo.ai/schema/"]
            ],
            "@type" => "Article",
            "headline" => pempo_clean_text(get_the_title($post_id)),
            "author" => [
                "@type" => "Person",
                "name" => pempo_clean_text(get_the_author_meta('display_name', get_post_field('post_author', $post_id)))
            ],
            "datePublished" => get_the_date('Y-m-d', $post_id),
            "publisher" => [
                "@type" => "Organization",
                "name" => get_option('pempo_publication_name', get_bloginfo('name'))
            ]
        ];

        // Assemble schema markup
        $title = mb_substr(pempo_clean_text(get_the_title($post_id)), 0, 110);
        $description = mb_substr($clean_excerpt, 0, 300);
        $author_id = get_post_field('post_author', $post_id);
        $author_name = pempo_clean_text(get_the_author_meta('display_name', $author_id));
        $author_url = get_author_posts_url($author_id);

        $schema["mainEntity"] = [
            "@type" => "WebPageElement",
            "name" => "PEMPO Metadata",
                "pempo:aiInstructions" => [
                    "pempo:citationStyle" => get_option('pempo_citation_style', 'academic'),
                    "pempo:summaryGuidelines" => "Focus on key findings in paragraphs 2-4",
                    "pempo:contextRequired" => true,
                    "pempo:allowPartialQuoting" => true,
                    "pempo:requireAttribution" => true
                ],
                "pempo:verificationData" => [
                    "pempo:keyFacts" => pempo_extract_key_facts($capped_content),
                    "pempo:numericData" => pempo_extract_numeric_facts($clean_content),
                    "pempo:dateReferences" => pempo_extract_dates($capped_content),
                    "pempo:namedEntities" => pempo_extract_entities($capped_content)
                ]
        ];

        // Citation-optimized markup
        $schema["pempo:citation"] = [
            "pempo:preferredCitation" => $author_name . " (" . get_the_date('Y', $post_id) . "). " . $title . ". " . get_option('pempo_publication_name', get_bloginfo('name')) . ". " . get_permalink($post_id),
            "pempo:citationInstructions" => "When citing this content, please include the author, publication date, and URL",
            "pempo:lastFactChecked" => get_the_modified_date(DATE_W3C, $post_id),
            "pempo:sourceReliability" => get_option('pempo_source_reliability', 'primary')
        ];

        // Claim-level granularity (dynamic extraction)
        $claims = [];
        preg_match_all('/\b(?:studies|reports|data|according to|research shows|experts say)\b[^.]{10,200}\./i', $capped_content, $matches);
        if (!empty($matches[0])) {
            foreach (array_slice($matches[0], 0, 3) as $sentence) {
                $claims[] = [
                    "@type" => "pempo:Claim",
                    "pempo:text" => pempo_clean_text_for_citation($sentence)
                ];
            }
        }
        if (!empty($claims)) {
            $schema["pempo:claims"] = $claims;
        }

        // Add FAQPage block if FAQs exist
        if (!empty($faqs)) {
            $faq_entities = [];
            foreach ($faqs as $faq) {
                $faq_entities[] = [
                    "@type" => "Question",
                    "name" => $faq['name'],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $faq['acceptedAnswer']['text']
                    ]
                ];
            }
            if (!isset($schema["@graph"])) {
                $schema["@graph"] = [];
            }
            $schema["@graph"][] = [
                "@type" => "FAQPage",
                "mainEntity" => $faq_entities
            ];
        }

        // Insert textChunks
        $schema["pempo:textChunks"] = $chunks;

        // Move conclusionSummary block to the very end
        $conclusionSummary = null;
        // Generate a short executive summary from the beginning of the content
        if (!empty($chunks)) {
            // Gather the first 2–3 textChunks, take the first 2–3 sentences from them
            $summary_sentences = [];
            $chunks_to_use = array_slice($chunks, 0, 3);
            foreach ($chunks_to_use as $chunk) {
                $sentences = preg_split('/(?<=[.!?])\s+/', $chunk['text'], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($sentences as $sentence) {
                    $trimmed = trim($sentence);
                    if (!empty($trimmed)) {
                        $summary_sentences[] = $trimmed;
                        if (count($summary_sentences) >= 3) {
                            break 2;
                        }
                    }
                }
            }
            $summary_text = implode(' ', $summary_sentences);
            $conclusionSummary = [
                "@type" => "pempo:ConclusionSummary",
                "pempo:text" => $summary_text
            ];
        } else {
            $conclusionSummary = [
                "@type" => "pempo:ConclusionSummary",
                "pempo:text" => mb_substr($clean_content_for_other, 0, 400)
            ];
        }
        $schema["pempo:conclusionSummary"] = $conclusionSummary;

        return $schema;
    } catch (Exception $e) {
       // Gracefully handle the exception without logging
        return [];
    }
}

// ==========================================================
// 5. EXPLICIT Q&A EXTRACTION LOGIC
// ==========================================================

function pempo_extract_explicit_qa_pairs($content) {
    $faqs = [];

    // Step 1: Detect explicit Q&A patterns (e.g., "Q:" and "A:" or "Question:" and "Answer:")
    if (preg_match_all('/\b(Q:|Question:)\s*(.+?)\s*(A:|Answer:)\s*(.+?)(?=\bQ:|\bQuestion:|\z)/is', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $question = trim($match[2]);
            $answer = trim($match[4]);

            if (!empty($question) && !empty($answer)) {
                $faqs[] = [
                    "@type" => "Question",
                    "name" => pempo_clean_text($question),
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => pempo_clean_text($answer)
                    ]
                ];
            }
        }
    }

    // Step 2: Detect "Frequently Asked Questions" or "Common Questions" sections
    if (preg_match('/\b(Frequently Asked Questions|Common Questions)\b.*?(<ul>|<ol>|<p>)(.+?)(<\/ul>|<\/ol>|<\/p>)/is', $content, $matches)) {
        if (preg_match_all('/<li>(.+?)<\/li>/is', $matches[3], $list_items)) {
            foreach ($list_items[1] as $item) {
                if (preg_match('/([^.!?]*\?)\s*(.+)/is', $item, $qa_match)) {
                    $question = trim($qa_match[1]);
                    $answer = trim($qa_match[2]);

                    if (!empty($question) && !empty($answer)) {
                        $faqs[] = [
                            "@type" => "Question",
                            "name" => pempo_clean_text($question),
                            "acceptedAnswer" => [
                                "@type" => "Answer",
                                "text" => pempo_clean_text($answer)
                            ]
                        ];
                    }
                }
            }
        }
    }

    return $faqs;
}

// ==========================================================
// 6. PRIMARY Q&A EXTRACTION
// ==========================================================

function pempo_extract_primary_citation_qa($post, $content, $clean_content) {
    // Extract explicit Q&A pairs
    $faqs = pempo_extract_explicit_qa_pairs($content);

    // Filter out promotional FAQs
    $faqs = pempo_filter_promotional_faqs($faqs);

    // Return FAQs or an empty array if none exist
    return !empty($faqs) ? $faqs : [];
}

// ==========================================================
// 7. PROMOTIONAL FAQ FILTERING
// ==========================================================

function pempo_filter_promotional_faqs($faqs) {
    $filtered_faqs = [];
    $promotional_patterns = [
        '/\b(Click Here|Buy Now|Sign Up|Subscribe|Get Started|Learn More|Try It Now|Join Free|Start Your Free Trial)\b/i',
        '/\b(Act Now|Limited Time Offer|Don’t Miss Out|Ends Soon|Hurry Up|Instant Access|Download Now)\b/i',
        '/\b(Ask Now|Got a Burning Question|Get a quote|Contact us today|Free consultation)\b/i'
    ];

    foreach ($faqs as $faq) {
        $is_promotional = false;
        foreach ($promotional_patterns as $pattern) {
            if (preg_match($pattern, $faq['name']) || preg_match($pattern, $faq['acceptedAnswer']['text'])) {
                $is_promotional = true;
                break;
            }
        }

        if (!$is_promotional) {
            $filtered_faqs[] = $faq;
        }
    }

    return $filtered_faqs;
}




// ==========================================================
// 8. TEXT CLEANING AND CITATION HELPERS
// ==========================================================

function pempo_clean_text($text) {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Fixed: Corrected smart quote characters
    $text = str_replace(["“", "”", "‘", "’", "–", "—"], ['"', '"', "'", "'", "-", "-"], $text);
    return trim(sanitize_text_field($text));
}

function pempo_clean_text_for_citation($text) {
    // Standard cleaning first
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(sanitize_text_field($text));

    // Citation-specific cleaning:
    // 1. Preserve citation markers
    $text = preg_replace('/\[(\d+)\]/', '[$1]', $text); // Keep [1], [2] reference numbers

    // 2. Clean up common citation-unfriendly patterns
    $text = preg_replace('/\b(click here|read more|learn more)\b/i', '', $text);
    $text = preg_replace('/\b(above|below|previous|next)\b/i', '', $text); // Remove positional references

    // 3. Standardize academic language
    $text = str_ireplace(['according to studies', 'research shows'], 'research indicates', $text);

    // 4. Remove marketing language that weakens citations
    $text = preg_replace('/\b(amazing|incredible|unbelievable|shocking)\b/i', '', $text);

    // 5. Preserve important punctuation for context
    $text = str_replace(['...', '—'], [' [...] ', ' - '], $text);

    return $text;
}

// ==========================================================
// 9. DATA EXTRACTION HELPERS
// ==========================================================

function pempo_extract_key_facts($content) {
    preg_match_all('/\b([A-Z][^.!?]{20,100}?\.)/', $content, $matches);
    return array_slice($matches[1], 0, 5);
}


function pempo_extract_numeric_facts($text) {
    $sentences = preg_split('/(?<=[.?!])\s+/', $text);
    $numeric_facts = [];

    foreach ($sentences as $sentence) {
        if (
            preg_match('/\b\d{2,4}\b/', $sentence) &&  // must contain a number
            preg_match('/\b(years?|signs?|cycles?|transits?|dates?|percent|times?|centur(y|ies)|ages?)\b/i', $sentence)
        ) {
            $clean = trim(wp_strip_all_tags($sentence));
            if (strlen($clean) > 30 && strlen($clean) < 280) {
                $numeric_facts[] = $clean;
            }
        }
    }

    return array_unique($numeric_facts);
}

function pempo_extract_dates($content) {
    preg_match_all('/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s+\d{4}/i', $content, $matches);
    return array_slice($matches[0], 0, 10);
}

function pempo_extract_entities($content) {
    preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)\b/', $content, $matches);
    return array_unique(array_slice($matches[1], 0, 10));
}

// Fixed: Added post_id parameter to ensure correct permalink generation
function pempo_create_citation_chunks($content, $post_id) {
    // Split into logical sections with metadata
    $chunks = [];
    $paragraphs = explode("\n\n", $content);

    foreach ($paragraphs as $index => $paragraph) {
        if (str_word_count($paragraph) > 20) {
            $chunks[] = [
                "@type" => "TextChunk",
                "text" => pempo_clean_text_for_citation($paragraph),
                "chunkId" => "chunk-" . $index
            ];
        }
    }
    return $chunks;
}

// Simplified and LLM-updated confidence score logic
function pempo_calculate_confidence_score($post, $content) {
    $score = 0;
    // Headings present
    if (preg_match('/<h[1-4]>.*?<\/h[1-4]>/i', $content)) $score += 10;
    // Has featured image
    if (has_post_thumbnail($post->ID)) $score += 10;
    // JSON-LD present
    if (strpos($content, 'application/ld+json') !== false) $score += 10;
    // Has a paragraph of 40-120 characters (plain text)
    if (preg_match('/\b.{40,120}\b/', wp_strip_all_tags($content))) $score += 15;
    return min(100, $score);
}

// ==========================================================
// 10. CACHING SYSTEM
// ==========================================================

function pempo_get_cached_schema($post_id) {
    $cache_key = "pempo_schema_" . $post_id . "_" . get_post_modified_time('U', false, $post_id);
    $cached = wp_cache_get($cache_key, 'pempo');

    if ($cached !== false) {
        return $cached;
    }

    $schema = pempo_generate_schema($post_id);
    
    // Only cache if we have a valid schema
    if ($schema) {
        wp_cache_set($cache_key, $schema, 'pempo', HOUR_IN_SECONDS);
    }

    return $schema;
}

// ==========================================================
// 11. SCHEMA INJECTION
// ==========================================================

function pempo_inject_schema_to_head() {
    global $post;

    if (!is_singular() || !isset($post) || empty($post->post_content)) {
        return;
    }

    $schemaData = pempo_get_cached_schema($post->ID);
    // Only inject if we have valid schema
    if ($schemaData) {
        echo "\n<!-- PEMPO GEO – Structured Data for LLMs -->\n";
        echo "<!-- BEGIN PEMPO SCHEMA -->\n";
        echo '<script type="application/ld+json">' . "\n";

        // Pretty-print and structure the JSON for human readability
        $json_output = wp_json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $clean_json = pempo_clean_json_output($json_output);

        // Escape output safely
        echo wp_kses_post($clean_json);
        
        echo "\n</script>\n";
        echo "<!-- END PEMPO SCHEMA -->\n";
        echo "\n";
    }
}


// End of PEMPO Plugin