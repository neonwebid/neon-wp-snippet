<?php
declare(strict_types=1);
namespace NeonWebId\WP\Snippet\Classes;

use WP_Customize_Manager;
use WP_Post;
use WP_Query;
use function __;
use function get_theme_mod;
use function add_action;
use function add_filter;
use function is_single;
use function str_contains;
use function parse_blocks;
use function render_block;
use function wpautop;
use function explode;
use function add_query_arg;
use function get_permalink;
use function wp_link_pages;

/**
 * Class AutoPageBreak
 *
 * @package NeonWebId\WP\Snippet\Classes
 * @author Neon WordPress Developer
 * @url https://www.neon.web.id/
 */
class AutoPageBreak
{

    public static ?AutoPageBreak $run = null;
    protected static bool $autoloaderIsRegistered = false;
    private string $pageBreak = '<!--nextpage-->';
    private int $pageBreakAtParagraph = 3;

    public function __construct()
    {
        $this->registerAutoload();
    }

    private function registerAutoload()
    {
        // prevent multiple registrations
        if (self::$autoloaderIsRegistered) {
            return;
        }

        self::$autoloaderIsRegistered = true;

        add_action('customize_register', [$this, 'pageBreakOption']);
        add_action('the_posts', [$this, 'autoPageBreak'], 90, 2);
        add_filter('the_content', [$this, 'pageBreakPagination'], 90);
        add_filter('wp_link_pages_args', [$this, 'paginationConfig']);
        add_action('wp_head', [$this, 'pageBreakPaginationStyle']);
    }

    public static function run(): AutoPageBreak
    {
        if ( ! self::$run instanceof self) {
            self::$run = new self();
        }

        return self::$run;
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     *
     * @return void
     */
    public function pageBreakOption(WP_Customize_Manager $wp_customize)
    {

        $wp_customize->add_section('page_break_option', [
            'title'       => __('PageBreak Option', 'neon-web-id'),
            'description' => __('Setting Page Break', 'neon-web-id'),
            'priority'    => 160,
        ]);

        $wp_customize->add_setting('use_auto_page_break', [
            'default'           => false,
            'type'              => 'option',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control('use_auto_page_break', [
            'label'   => __('Use AutoPageBreak', 'neon-web-id'),
            'section' => 'page_break_option',
            'type'    => 'checkbox',
        ]);

        $wp_customize->add_setting('page_break_at_paragraph', [
            'default'           => $this->pageBreakAtParagraph,
            'type'              => 'option',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control('page_break_at_paragraph', [
            'label'           => __('Break after Paragraph', 'neon-web-id'),
            'section'         => 'page_break_option',
            'type'            => 'number',
            'input_attrs'     => array(
                'min'  => 3,
                'step' => 1,
            ),
            'active_callback' => [$this, 'activeCallbackDependency'],
        ]);

        $wp_customize->add_setting('page_break_name', [
            'default'           => __('Page', 'neon-web-id'),
            'type'              => 'option',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control('page_break_name', [
            'label'           => __('Page Label', 'neon-web-id'),
            'section'         => 'page_break_option',
            'type'            => 'text',
            'transport'       => 'refresh',
            'active_callback' => [$this, 'activeCallbackDependency'],
        ]);

        $wp_customize->add_setting('page_break_show_all', [
            'default'           => __('Show All', 'neon-web-id'),
            'type'              => 'option',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control('page_break_show_all', [
            'label'           => __('Show All Label', 'neon-web-id'),
            'section'         => 'page_break_option',
            'type'            => 'text',
            'active_callback' => [$this, 'activeCallbackDependency'],
        ]);
    }

    /**
     * @param WP_Post $posts
     * @param WP_Query $query
     *
     * @return WP_Post
     */
    public function autoPageBreak(WP_Post $posts, WP_Query $query): WP_Post
    {

        $is_main_query_single = $query->is_main_query() && $query->is_single();

        if (($is_main_query_single || is_single()) && $this->activeCallbackDependency()) {
            $break_at_paragraph = get_theme_mod('page_break_at_paragraph', $this->pageBreakAtParagraph);
            foreach ($posts as $post) {
                $post->post_content = $this->setPageBreak($post->post_content, $break_at_paragraph);
            }
        }

        return $posts;
    }

    /**
     * @return bool
     */
    public function activeCallbackDependency(): bool
    {
        $isUsePageBreak = get_theme_mod('use_auto_page_break');
        return (int)$isUsePageBreak > 0;
    }

    /**
     * set <!--nextpage--> to the content
     *
     * @param string $postContent
     * @param int $breakAtParagraph
     *
     * @return string
     */
    protected function setPageBreak(string $postContent, int $breakAtParagraph = 0): string
    {
        // stop adding <!--nextpage--> if the content already has it
        if (empty($breakAtParagraph) || str_contains($postContent, $this->pageBreak)) {
            return $postContent;
        }

        $_content = '';
        $n        = 0;
        $blocks   = parse_blocks($postContent);

        // check if the content is using Gutenberg
        if ($blocks) {
            foreach ($blocks as $block) {
                if ( ! empty($block['blockName']) && $block['blockName'] == 'core/paragraph') {
                    $n++;
                }

                $_content .= render_block($block);
                if ($n == $breakAtParagraph) {
                    $_content .= $this->pageBreak;
                    $n        = 0;
                }

            }
        }

        // make sure <!--nextpage--> has been added
        if (! str_contains($_content, $this->pageBreak)) {
            $_content = '';
            if ( ! str_contains($postContent, '</p>')) {
                $postContent = wpautop($postContent);
            }

            $paragraphs = explode('</p>', $postContent);
            foreach ($paragraphs as $paragraph) {
                $_content .= $paragraph . '</p>';

                if ($n == $breakAtParagraph) {
                    $_content .= $this->pageBreak;
                    $n        = 0;
                }

                $n++;
            }
        }

        return $_content;
    }


    public function paginationConfig(): array
    {
        return $this->paginationArgs();
    }

    protected function paginationArgs(): array
    {
        global $post;

        $beforePrefixText = get_theme_mod('page_break_name', 'Halaman');
        $before             = '<div class="neon-auto-page-break-pagination"><div class="pagination"><span class="prefix">' . $beforePrefixText . ': </span>';

        $linkShowAll = add_query_arg([
            'show' => 'all'
        ], get_permalink($post));

        $afterPrefixText = get_theme_mod('page_break_show_all', 'Lihat Semua');
        $after             = '</div><div class="neon-show-all-page-content"><a href="' . $linkShowAll . '">' . $afterPrefixText . '</a></div>';

        return [
            'before'           => $before,
            'after'            => $after,
            'link_before'      => '<span class="page-number">',
            'link_after'       => '</span>',
            'next_or_number'   => 'number',
            'separator'        => ' ',
            'aria_current'     => 'page',
            'nextpagelink'     => __('Next page'),
            'previouspagelink' => __('Previous page'),
            'pagelink'         => '%',
            'echo'             => false
        ];
    }

    /**
     * enable pagination on the_content
     * 
     * @param string $content
     *
     * @return string
     */
    public function pageBreakPagination(string $content):string
    {
        global $multipage, $post;
        if ( ! empty($_GET['show']) && $_GET['show'] == 'all') {
            return $post->post_content;
        }

        if (
            $multipage &&
            $this->activeCallbackDependency() &&
            ! str_contains($content, '<div class="neon-auto-page-break-pagination">')
        ) {
            $content .= wp_link_pages($this->paginationArgs());
        }

        return $content;
    }

    public function pageBreakPaginationStyle()
    {
        ?>
        <style>
            .neon-auto-page-break-pagination {
                display: flex;
                flex-wrap: wrap;
            }

            .neon-auto-page-break-pagination div {
                flex: 1 1 50%;
            }

            .neon-auto-page-break-pagination a {
                text-decoration: none;
            }

            .neon-auto-page-break-pagination .neon-show-all-page-content {
                text-align: right;
            }

            .neon-auto-page-break-pagination .current {
                font-weight: bold;
            }

            @media (max-width: 500px) {
                .neon-auto-page-break-pagination div {
                    flex-basis: 100%;
                    text-align: center;
                }
            }
        </style>
        <?php
    }
}

AutoPageBreak::run();
