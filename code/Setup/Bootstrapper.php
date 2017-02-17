<?php
namespace WPOrbit\MetaBoxes\Setup;

use WPOrbit\MetaBoxes\Ajax\PostPivotMetaBoxAjaxController;

class Bootstrapper
{
    protected function admin_scripts()
    {
        add_action( 'admin_enqueue_scripts', function()
        {
            // Declare path to assets.
            $path = plugin_dir_url( __FILE__ ) . '../../assets/';

            // Posts pivoter view model.
            wp_register_script(
                'wp-orbit-posts-pivoter-view-model',
                $path . 'js/posts-pivoter-view-model.js'
            );
        });
    }

    protected function ajax_controllers()
    {
        new PostPivotMetaBoxAjaxController;
    }

    public function __construct()
    {
        $this->admin_scripts();
        $this->ajax_controllers();
    }
}