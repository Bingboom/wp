<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Models\Page_Author_Archive;
use IAWP_SCOPED\IAWP\Models\Page_Post_Type_Archive;
use IAWP_SCOPED\IAWP\Models\Page_Singular;
use IAWP_SCOPED\IAWP\Models\Page_Term_Archive;
class Track_Resource_Changes
{
    public function __construct()
    {
        \add_action('post_updated', [$this, 'handle_updated_post']);
        \add_action('profile_update', [$this, 'handle_updated_author']);
        \add_action('registered_post_type', [$this, 'handle_registered_post_type']);
        \add_action('edit_term', [$this, 'handle_updated_term']);
    }
    public function handle_updated_post($post_id)
    {
        $post = \get_post($post_id);
        if (isset($post) && $post->post_status != 'trash') {
            $row = (object) ['resource' => 'singular', 'singular_id' => $post_id];
            $page = new Page_Singular($row);
            $page->update_cache();
        }
    }
    public function handle_updated_author($user_id)
    {
        $row = (object) ['resource' => 'author', 'author_id' => $user_id];
        $page = new Page_Author_Archive($row);
        $page->update_cache();
    }
    public function handle_registered_post_type($post_type)
    {
        $post_type_object = \get_post_type_object($post_type);
        if ($post_type_object->_builtin == \false) {
            $row = (object) ['resource' => 'post_type_archive', 'post_type' => $post_type];
            $page = new Page_Post_Type_Archive($row);
            $page->update_cache();
        }
    }
    // Works for tag, categories, and custom taxonomies. Keep in mind that terms for custom taxonomies might just
    //   disappear if the custom taxonomy is not registered the next time around.
    public function handle_updated_term($term_id)
    {
        global $wpdb;
        // Term must be cleared from the cache in order to use the new term data
        \clean_term_cache($term_id);
        $row = (object) ['term_id' => $term_id];
        $page = new Page_Term_Archive($row);
        $page->update_cache();
        $posts = \get_posts(['post_type' => \get_post_types(), 'category' => $term_id, 'numberposts' => -1]);
        // Update cache for all singulars associated with a given term
        foreach ($posts as $post) {
            $row = (object) ['resource' => 'singular', 'singular_id' => $post->ID];
            $page = new Page_Singular($row);
            $page->update_cache();
        }
    }
}
