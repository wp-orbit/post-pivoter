<?php
namespace WPOrbit\MetaBoxes;

/**
 * Registers a meta box to an array of post types.
 *
 * Class MetaBox
 * @package WPOrbit\MetaBoxes
 */
class MetaBox
{
    /**
     * @var string The meta box ID.
     */
    protected $id;

    /**
     * @var string The meta box label.
     */
    protected $label;

    /**
     * @var array An array of post types this meta box should be hooked into.
     */
    protected $postTypes = [];

    public function __construct( $args )
    {
        if ( isset( $args['id'] ) ) {
            $this->id = $args['id'];
        }
        if ( isset( $args['label'] ) ) {
            $this->label = $args['label'];
        }
        if ( isset( $args['postTypes'] ) ) {
            $this->postTypes = $args['postTypes'];
        }

        $this->register();
    }

    public function render($post)
    {
        echo 'Override the render function.';
    }

    public function save($postId)
    {
        // Override save function..
    }

    public function register()
    {
        foreach( $this->postTypes as $postType )
        {
            add_action( "add_meta_boxes_{$postType}", function($post)
            {
                add_meta_box(
                    $this->id,
                    $this->label,
                    [$this, 'render'],
                    null,
                    'normal',
                    'default'
                );
            });

            add_action( 'save_post', function($postId) use ($postType)
            {
                $this->save($postId);
            });
        }
    }
}